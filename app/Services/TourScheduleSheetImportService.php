<?php

namespace App\Services;

use App\Models\Tour;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class TourScheduleSheetImportService
{
    /** @var array<string, string> */
    private const REQUIRED_COLUMNS = [
        'name' => 'TEN TOUR',
        'schedule' => 'LICH KHOI HANH',
        'price' => 'GIA TRON GOI',
        'seats' => 'SO CHO',
    ];

    /** @var array<string, string> */
    private const OPTIONAL_COLUMNS = [
        'duration' => 'THOI GIAN',
        'transport' => 'PHUONG TIEN DI CHUYEN',
        'commission' => 'COM',
    ];

    public function __construct(private readonly HgTripXlsxReader $reader) {}

    /** @return array<string, mixed> */
    public function preview(string $path, int $defaultYear): array
    {
        return $this->previewRows($this->reader->readRows($path), $defaultYear);
    }

    /**
     * @param array<string, list<array{row: int, cells: array<int, string>}>> $sheets
     * @return array<string, mixed>
     */
    public function previewRows(array $sheets, int $defaultYear): array
    {
        foreach ($sheets as $sheetName => $rows) {
            $header = $this->findHeader($rows);

            if ($header === null) {
                continue;
            }

            return $this->previewSheet($sheetName, $rows, $header, $defaultYear);
        }

        throw new RuntimeException('Không tìm thấy hàng tiêu đề theo mẫu: TÊN TOUR, LỊCH KHỞI HÀNH, GIÁ TRỌN GÓI và SỐ CHỖ.');
    }

    /**
     * @param array<string, mixed> $preview
     * @param array<string, int|string|null> $mappings
     * @return array{tours: int, created: int, updated: int, skipped: int}
     */
    public function import(array $preview, array $mappings): array
    {
        $sources = $preview['sources'] ?? [];

        if (! is_array($sources) || $sources === []) {
            throw new RuntimeException('Phiên đối chiếu lịch khởi hành không còn hợp lệ. Hãy tải lại file và kiểm tra lại.');
        }

        $selectedIds = collect($mappings)
            ->filter(fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $tours = Tour::query()->whereKey($selectedIds)->get()->keyBy('id');
        $result = ['tours' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($sources, $mappings, $tours, &$result): void {
            foreach ($sources as $sourceKey => $source) {
                if (! is_array($source)) {
                    continue;
                }

                $tourId = isset($mappings[$sourceKey]) && is_numeric($mappings[$sourceKey])
                    ? (int) $mappings[$sourceKey]
                    : 0;

                if ($tourId <= 0) {
                    $result['skipped']++;
                    continue;
                }

                $tour = $tours->get($tourId);

                if (! $tour instanceof Tour) {
                    throw new RuntimeException('Tour được chọn trong bước đối chiếu không còn tồn tại.');
                }

                $schedules = $source['schedules'] ?? [];

                if (! is_array($schedules) || $schedules === []) {
                    $result['skipped']++;
                    continue;
                }

                $byDate = [];
                foreach ($schedules as $schedule) {
                    if (is_array($schedule) && filled($schedule['departure_date'] ?? null)) {
                        $byDate[(string) $schedule['departure_date']] = $schedule;
                    }
                }

                $existing = $tour->schedules()
                    ->withCount('bookingItems')
                    ->whereIn('departure_date', array_keys($byDate))
                    ->get()
                    ->keyBy(fn ($schedule): string => $schedule->departure_date->toDateString());

                foreach ($byDate as $date => $schedule) {
                    $current = $existing->get($date);
                    $seatsTotal = max(0, (int) ($schedule['seats_total'] ?? 0));
                    $reserved = (int) ($current?->seats_reserved ?? 0);

                    if ($seatsTotal > 0 && $seatsTotal < $reserved) {
                        throw new RuntimeException("{$tour->name} ngày {$date}: số chỗ trong file thấp hơn {$reserved} chỗ đã giữ.");
                    }

                    $payload = [
                        'return_date' => $schedule['return_date'] ?? $current?->return_date?->toDateString(),
                        'seats_total' => $seatsTotal,
                        'price' => (int) ($schedule['price'] ?? 0),
                        'transport' => $schedule['transport'] ?? $current?->transport,
                        'source_seat_info_raw' => $schedule['source_seat_info_raw'] ?? null,
                        'source_seat_interpretation' => 'Số chỗ tổng theo cột SỐ CHỖ',
                        'source_sheet' => $schedule['source_sheet'] ?? null,
                        'source_row' => $schedule['source_row'] ?? null,
                        'source_date_raw' => $schedule['source_date_raw'] ?? null,
                        'source_name_raw' => $schedule['source_name_raw'] ?? null,
                    ];

                    if ($current) {
                        $current->update($payload);
                        $result['updated']++;
                    } else {
                        $tour->schedules()->create($payload + [
                            'departure_date' => $date,
                            'seats_reserved' => 0,
                            'status' => 'open',
                        ]);
                        $result['created']++;
                    }
                }

                $result['tours']++;
            }
        });

        return $result;
    }

    /**
     * @param list<array{row: int, cells: array<int, string>}> $rows
     * @return array{index: int, row: int, columns: array<string, int>}|null
     */
    private function findHeader(array $rows): ?array
    {
        foreach ($rows as $index => $row) {
            $columns = [];

            foreach ($row['cells'] as $column => $value) {
                $normalised = $this->normaliseHeader($value);

                foreach (self::REQUIRED_COLUMNS + self::OPTIONAL_COLUMNS as $field => $label) {
                    if ($normalised === $label) {
                        $columns[$field] = $column;
                    }
                }
            }

            if (count(array_intersect_key(self::REQUIRED_COLUMNS, $columns)) === count(self::REQUIRED_COLUMNS)) {
                return ['index' => $index, 'row' => $row['row'], 'columns' => $columns];
            }
        }

        return null;
    }

    /**
     * @param list<array{row: int, cells: array<int, string>}> $rows
     * @param array{index: int, row: int, columns: array<string, int>} $header
     * @return array<string, mixed>
     */
    private function previewSheet(string $sheetName, array $rows, array $header, int $defaultYear): array
    {
        $sources = [];
        $errors = [];
        $warnings = [];
        $current = ['name' => null, 'duration' => null, 'transport' => null];

        foreach (array_slice($rows, $header['index'] + 1) as $row) {
            $cells = $row['cells'];
            $name = $this->cell($cells, $header['columns'], 'name');

            if ($name !== '') {
                $current = [
                    'name' => $name,
                    'duration' => $this->cell($cells, $header['columns'], 'duration'),
                    'transport' => $this->cell($cells, $header['columns'], 'transport'),
                ];
            } elseif ($current['name'] !== null) {
                $transport = $this->cell($cells, $header['columns'], 'transport');
                if ($transport !== '') {
                    $current['transport'] = $transport;
                }
            }

            $sourceDate = $this->cell($cells, $header['columns'], 'schedule');

            if ($sourceDate === '') {
                continue;
            }

            if ($current['name'] === null) {
                $errors[] = "Dòng {$row['row']}: có lịch khởi hành nhưng không xác định được tên tour.";
                continue;
            }

            $price = $this->money($this->cell($cells, $header['columns'], 'price'));
            $seats = $this->integer($this->cell($cells, $header['columns'], 'seats'));

            if ($price === null || $price <= 0) {
                $errors[] = "Dòng {$row['row']} ({$current['name']}): giá trọn gói chưa hợp lệ.";
                continue;
            }

            if ($seats === null || $seats < 0 || $seats > 65535) {
                $errors[] = "Dòng {$row['row']} ({$current['name']}): số chỗ chưa hợp lệ.";
                continue;
            }

            $dates = $this->dates($sourceDate, $defaultYear);
            if ($dates === []) {
                $errors[] = "Dòng {$row['row']} ({$current['name']}): không đọc được ngày trong “{$sourceDate}”.";
                continue;
            }

            $sourceKey = sha1($this->normaliseName($current['name']));
            $sources[$sourceKey] ??= [
                'key' => $sourceKey,
                'name' => $current['name'],
                'duration' => $current['duration'],
                'transport' => $current['transport'],
                'schedules' => [],
            ];

            $durationDays = $this->durationDays((string) ($current['duration'] ?: $current['name']));
            $commission = $this->cell($cells, $header['columns'], 'commission');
            if ($commission !== '') {
                $warnings[] = "Dòng {$row['row']}: cột COM được giữ nguyên trong file nguồn, chưa ghi vào giá tour.";
            }

            foreach ($dates as $date) {
                $sources[$sourceKey]['schedules'][] = [
                    'departure_date' => $date->toDateString(),
                    'return_date' => $durationDays ? $date->addDays($durationDays - 1)->toDateString() : null,
                    'seats_total' => $seats,
                    'price' => $price,
                    'transport' => $current['transport'] ?: null,
                    'source_seat_info_raw' => $this->cell($cells, $header['columns'], 'seats'),
                    'source_sheet' => $sheetName,
                    'source_row' => $row['row'],
                    'source_date_raw' => $sourceDate,
                    'source_name_raw' => $current['name'],
                ];
            }
        }

        if ($errors !== []) {
            throw new RuntimeException("Không thể đọc bảng lịch khởi hành:\n- ".implode("\n- ", $errors));
        }

        if ($sources === []) {
            throw new RuntimeException('Không có lịch khởi hành hợp lệ trong file.');
        }

        return [
            'sheet_name' => $sheetName,
            'header_row' => $header['row'],
            'default_year' => $defaultYear,
            'sources' => $sources,
            'schedule_count' => array_sum(array_map(fn (array $source): int => count($source['schedules']), $sources)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    /** @param array<int, string> $cells @param array<string, int> $columns */
    private function cell(array $cells, array $columns, string $field): string
    {
        return isset($columns[$field]) ? trim((string) ($cells[$columns[$field]] ?? '')) : '';
    }

    /** @return list<CarbonImmutable> */
    private function dates(string $value, int $defaultYear): array
    {
        preg_match_all('/tháng\s*(?<month>0?[1-9]|1[0-2])\s*(?:\/\s*(?<year>20\d{2}))?\s*:\s*(?<days>.*?)(?=tháng\s*(?:0?[1-9]|1[0-2])\s*(?:\/\s*20\d{2})?\s*:|$)/iu', $value, $matches, PREG_SET_ORDER);
        $dates = [];

        foreach ($matches as $match) {
            $month = (int) $match['month'];
            $year = filled($match['year'] ?? null) ? (int) $match['year'] : $defaultYear;
            preg_match_all('/(?<!\d)(?<day>[0-3]?\d)(?!\d)/u', (string) $match['days'], $days, PREG_SET_ORDER);

            foreach ($days as $dayMatch) {
                $day = (int) $dayMatch['day'];
                if (checkdate($month, $day, $year)) {
                    $dates[] = CarbonImmutable::create($year, $month, $day);
                }
            }
        }

        return collect($dates)->unique(fn (CarbonImmutable $date): string => $date->toDateString())->values()->all();
    }

    private function durationDays(string $value): ?int
    {
        if (preg_match('/(?<days>\d+)\s*N\s*\d+\s*[ĐD]/iu', $value, $matches) !== 1) {
            return null;
        }

        return max(1, (int) $matches['days']);
    }

    private function money(string $value): ?int
    {
        $value = Str::upper(trim($value));
        $value = str_replace([' ', '₫', 'Đ', 'VND'], '', $value);

        if ($value === '') {
            return null;
        }

        if (str_ends_with($value, 'K')) {
            $thousands = preg_replace('/[^0-9]/', '', substr($value, 0, -1));

            return $thousands !== '' ? (int) $thousands * 1000 : null;
        }

        $number = preg_replace('/[^0-9]/', '', $value);

        return $number !== '' ? (int) $number : null;
    }

    private function integer(string $value): ?int
    {
        $number = preg_replace('/[^0-9]/', '', $value);

        return $number !== '' ? (int) $number : null;
    }

    private function normaliseHeader(string $value): string
    {
        $value = Str::upper(Str::ascii(trim($value)));

        return trim((string) preg_replace('/[^A-Z0-9]+/', ' ', $value));
    }

    private function normaliseName(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', Str::upper(Str::ascii($value))));
    }
}
