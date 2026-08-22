<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourImage;
use App\Models\TourItinerary;
use App\Models\TourSchedule;
use App\Models\TourSection;
use App\Support\TourScheduleSeatInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

final class HgTripImportService
{
    private const PACKAGE_NAME = 'HGTRIP_IMPORT_FULL_2026-08-19';

    private const SECTION_TYPES = [
        'highlights',
        'pricing',
        'included',
        'excluded',
        'visa',
        'conditions',
        'notes',
        'other',
    ];

    public function __construct(private readonly HgTripXlsxReader $reader) {}

    /** @return array<string, mixed> */
    public function import(string $archivePath, bool $dryRun = false, bool $force = false): array
    {
        $archivePath = realpath($archivePath) ?: $archivePath;

        if (!is_file($archivePath)) {
            throw new RuntimeException("Import archive does not exist: {$archivePath}");
        }

        $staging = storage_path('app/.hgtrip-import/'.Str::uuid());
        File::ensureDirectoryExists($staging);

        try {
            $this->extractArchive($archivePath, $staging);
            $this->validatePackageFiles($staging);

            $manifest = json_decode((string) File::get($staging.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $sheets = $this->reader->read($staging.'/hgtrip_import.xlsx');
            $data = $this->prepareData($sheets);
            $this->validateData($data, $staging);

            $result = [
                'package' => $manifest['package'] ?? self::PACKAGE_NAME,
                'dry_run' => $dryRun,
                'tours' => count($data['tours']),
                'itineraries' => count($data['itineraries']),
                'schedules' => count($data['schedules']),
                'sections' => count($data['sections']),
                'images' => count(array_filter($data['images'], fn (array $row): bool => $this->toBool($row['import_default'] ?? '1'))),
                'raw_content' => count($data['raw_content']),
                'unresolved' => $data['unresolved'],
            ];

            if (!$dryRun) {
                $this->writePackage($data, $staging, $force);
            }

            return $result;
        } finally {
            File::deleteDirectory($staging);
        }
    }

    private function extractArchive(string $archivePath, string $destination): void
    {
        $zip = new ZipArchive();

        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException("Cannot open import archive: {$archivePath}");
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = str_replace('\\', '/', (string) $zip->getNameIndex($index));

            if ($name === '' || str_starts_with($name, '/') || str_contains($name, '../')) {
                $zip->close();
                throw new RuntimeException("Unsafe archive entry: {$name}");
            }
        }

        if (!$zip->extractTo($destination)) {
            $zip->close();
            throw new RuntimeException('Could not extract import archive.');
        }

        $zip->close();
    }

    private function validatePackageFiles(string $staging): void
    {
        foreach (['README.txt', 'manifest.json', 'hgtrip_import.xlsx'] as $file) {
            if (!is_file($staging.'/'.$file)) {
                throw new RuntimeException("Import package is missing {$file}.");
            }
        }
    }

    /** @param array<string, list<array<string, string>>> $sheets @return array<string, mixed> */
    private function prepareData(array $sheets): array
    {
        foreach (['TOURS', 'ITINERARIES', 'SCHEDULES', 'SECTIONS', 'IMAGES', 'RAW_CONTENT', 'UNRESOLVED'] as $sheet) {
            if (!array_key_exists($sheet, $sheets)) {
                throw new RuntimeException("Import workbook is missing the {$sheet} sheet.");
            }
        }

        $tours = array_values(array_filter($sheets['TOURS'], fn (array $row): bool => $this->toBool($row['import_enabled'] ?? '1')));
        $codes = array_values(array_filter(array_map(fn (array $row): string => trim($row['code'] ?? ''), $tours)));

        return [
            'tours' => $tours,
            'tour_codes' => $codes,
            'itineraries' => $this->onlyCodes($sheets['ITINERARIES'], $codes),
            'schedules' => $this->onlyCodes($sheets['SCHEDULES'], $codes),
            'sections' => $this->onlyCodes($sheets['SECTIONS'], $codes),
            'images' => $this->onlyCodes($sheets['IMAGES'], $codes),
            'raw_content' => $this->onlyCodes($sheets['RAW_CONTENT'], $codes),
            'unresolved' => $this->onlyCodes($sheets['UNRESOLVED'], $codes),
        ];
    }

    /** @param list<array<string, string>> $rows @param list<string> $codes @return list<array<string, string>> */
    private function onlyCodes(array $rows, array $codes): array
    {
        return array_values(array_filter($rows, static fn (array $row): bool => in_array(trim($row['tour_code'] ?? ''), $codes, true)));
    }

    /** @param array<string, mixed> $data */
    private function validateData(array $data, string $staging): void
    {
        $categoryIds = TourCategory::query()->where('is_active', true)->pluck('id', 'slug')->all();
        $destinationIds = Destination::query()->pluck('id', 'slug')->all();
        $errors = [];

        foreach ($data['tours'] as $row) {
            $code = trim($row['code'] ?? '');
            $category = $this->importCategorySlug($row, $categoryIds);
            $destination = trim($row['destination_slug'] ?? '');

            if ($code === '' || trim($row['name'] ?? '') === '' || trim($row['slug'] ?? '') === '') {
                $errors[] = "Tour {$code}: code, name and slug are required.";
            }

            if (!isset($categoryIds[$category])) {
                $errors[] = "Tour {$code}: active category slug {$category} was not found.";
            }

            if ($destination !== '' && !isset($destinationIds[$destination])) {
                $errors[] = "Tour {$code}: destination slug {$destination} was not found.";
            }

            $slugExists = Tour::withTrashed()
                ->where('slug', trim($row['slug'] ?? ''))
                ->where('code', '!=', $code)
                ->exists();

            if ($slugExists) {
                $errors[] = "Tour {$code}: slug {$row['slug']} belongs to another tour.";
            }
        }

        foreach ($data['images'] as $row) {
            if (!$this->toBool($row['import_default'] ?? '1')) {
                continue;
            }

            $relativePath = trim($row['relative_path'] ?? '');
            $code = trim($row['tour_code'] ?? '');
            $expectedPrefix = 'images/'.$code.'/';
            $sourcePath = $staging.'/'.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (!str_starts_with($relativePath, $expectedPrefix) || !is_file($sourcePath)) {
                $errors[] = "Image {$relativePath}: source file is missing or outside its tour folder.";
                continue;
            }

            $expectedHash = strtolower(trim($row['sha256'] ?? ''));

            if ($expectedHash !== '' && !hash_equals($expectedHash, strtolower(hash_file('sha256', $sourcePath)))) {
                $errors[] = "Image {$relativePath}: SHA256 does not match the manifest.";
            }
        }

        if ($errors !== []) {
            throw new RuntimeException("Import validation failed:\n- ".implode("\n- ", $errors));
        }
    }

    /** @param array<string, mixed> $data */
    private function writePackage(array $data, string $staging, bool $force): void
    {
        $archiveDirectory = storage_path('app/imports/'.self::PACKAGE_NAME);

        if (is_dir($archiveDirectory) && !$force) {
            throw new RuntimeException("Import snapshot already exists at {$archiveDirectory}; use --force to replace it.");
        }

        File::ensureDirectoryExists($archiveDirectory.'/raw');
        File::put($archiveDirectory.'/manifest.json', File::get($staging.'/manifest.json'));
        File::put($archiveDirectory.'/README.txt', File::get($staging.'/README.txt'));
        File::put($archiveDirectory.'/unresolved.json', json_encode($data['unresolved'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        foreach (File::files($staging.'/raw') as $file) {
            File::copy($file->getPathname(), $archiveDirectory.'/raw/'.$file->getFilename());
        }

        $itineraries = $this->groupByCode($data['itineraries']);
        $schedules = $this->groupByCode($data['schedules']);
        $sections = $this->groupByCode($data['sections']);
        $images = $this->groupByCode($data['images']);
        $rawContent = $this->groupByCode($data['raw_content']);
        $categoryIds = TourCategory::query()->where('is_active', true)->pluck('id', 'slug')->all();
        $destinationIds = Destination::query()->pluck('id', 'slug')->all();

        foreach ($data['tours'] as $row) {
            $code = trim($row['code']);

            DB::transaction(function () use ($row, $code, $itineraries, $schedules, $sections, $images, $rawContent, $staging, $categoryIds, $destinationIds): void {
                $tour = Tour::withTrashed()->where('code', $code)->first();

                if ($tour) {
                    $tour->restore();
                    $tour->update($this->tourPayload($row, $rawContent[$code][0] ?? null));
                } else {
                    $tour = Tour::create($this->tourPayload($row, $rawContent[$code][0] ?? null));
                }

                $category = $this->importCategorySlug($row, $categoryIds);
                $destination = trim($row['destination_slug'] ?? '');

                $tour->categories()->sync([$categoryIds[$category] => ['sort_order' => 1]]);
                $tour->destinations()->sync($destination !== '' ? [$destinationIds[$destination] => ['sort_order' => 1]] : []);

                $tour->itineraries()->delete();
                foreach ($itineraries[$code] ?? [] as $itinerary) {
                    TourItinerary::create([
                        'tour_id' => $tour->id,
                        'day_number' => max(1, $this->toInt($itinerary['day_number'] ?? '1')),
                        'title' => $this->nullableString($itinerary['title'] ?? null),
                        'description' => $this->nullableString($itinerary['description'] ?? null),
                        'meals' => $this->nullableString($itinerary['meals'] ?? null),
                        'accommodation' => $this->nullableString($itinerary['accommodation'] ?? null),
                        'sort_order' => $this->toInt($itinerary['sort_order'] ?? '0'),
                    ]);
                }

                $tour->schedules()->delete();
                foreach ($schedules[$code] ?? [] as $schedule) {
                    $seatInfoRaw = $this->nullableString($schedule['seat_info_raw'] ?? null);
                    $sourceSeats = TourScheduleSeatInfo::parseBookedTotal($seatInfoRaw);
                    $seatsTotal = max(0, $this->toInt($schedule['seats_total'] ?? '0'));
                    $seatsReserved = max(0, $this->toInt($schedule['seats_reserved'] ?? '0'));
                    $seatsReserved = $seatsTotal > 0 ? min($seatsReserved, $seatsTotal) : $seatsReserved;

                    if ($sourceSeats !== null) {
                        $seatsTotal = $sourceSeats['seats_total'];
                        $seatsReserved = $sourceSeats['seats_reserved'];
                    }

                    TourSchedule::create([
                        'tour_id' => $tour->id,
                        'departure_date' => $this->excelDate($schedule['departure_date'] ?? null),
                        'return_date' => $this->excelDate($schedule['return_date'] ?? null),
                        'seats_total' => $seatsTotal,
                        'seats_reserved' => $seatsReserved,
                        'price' => $this->nullableNumber($schedule['price'] ?? null) ?? (float) $tour->starting_price,
                        'sale_price' => $this->nullableNumber($schedule['sale_price'] ?? null),
                        'transport' => $this->nullableString($schedule['transport'] ?? null) ?? $tour->transport,
                        'source_seat_info_raw' => $seatInfoRaw,
                        'source_seat_interpretation' => $sourceSeats['interpretation'] ?? $this->nullableString($schedule['seat_interpretation'] ?? null),
                        'source_sheet' => $this->nullableString($schedule['source_sheet'] ?? null),
                        'source_row' => $this->toInt($schedule['source_row'] ?? '0') ?: null,
                        'source_date_raw' => $this->nullableString($schedule['source_date_raw'] ?? null),
                        'source_name_raw' => $this->nullableString($schedule['source_name_raw'] ?? null),
                        'status' => in_array(strtolower(trim($schedule['status'] ?? '')), ['open', 'closed'], true)
                            ? strtolower(trim($schedule['status']))
                            : 'open',
                    ]);
                }

                $tour->sections()->delete();
                foreach ($sections[$code] ?? [] as $section) {
                    $type = strtolower(trim($section['type'] ?? 'other'));
                    TourSection::create([
                        'tour_id' => $tour->id,
                        'type' => in_array($type, self::SECTION_TYPES, true) ? $type : 'other',
                        'title' => $this->nullableString($section['title'] ?? null),
                        'content' => $this->nullableString($section['content'] ?? null),
                        'sort_order' => $this->toInt($section['sort_order'] ?? '0'),
                        'source_ref' => $this->nullableString($section['source_ref'] ?? null),
                        'source_url' => $this->nullableString($section['source_url'] ?? null),
                    ]);
                }

                $tour->images()->delete();
                foreach ($images[$code] ?? [] as $image) {
                    if (!$this->toBool($image['import_default'] ?? '1')) {
                        continue;
                    }

                    $relativeSource = trim($image['relative_path'] ?? '');
                    $sourcePath = $staging.'/'.str_replace('/', DIRECTORY_SEPARATOR, $relativeSource);
                    $filename = basename($relativeSource);
                    $relativeTarget = 'media/tours/'.$code.'/'.$filename;
                    $targetPath = public_path($relativeTarget);

                    File::ensureDirectoryExists(dirname($targetPath));
                    File::copy($sourcePath, $targetPath);

                    TourImage::create([
                        'tour_id' => $tour->id,
                        'path' => str_replace('\\', '/', $relativeTarget),
                        'alt_text' => $this->nullableString($image['alt_text'] ?? null) ?? $tour->name,
                        'is_cover' => $this->toBool($image['is_cover'] ?? '0'),
                        'sort_order' => $this->toInt($image['sort_order'] ?? '0'),
                    ]);
                }
            });
        }
    }

    /** @param array<string, string> $row @param array<string, string>|null $raw */
    private function tourPayload(array $row, ?array $raw): array
    {
        return [
            'code' => trim($row['code']),
            'name' => trim($row['name']),
            'slug' => trim($row['slug']),
            'summary' => $this->nullableString($row['summary'] ?? null),
            'description' => $this->nullableString($row['description'] ?? null),
            'duration_days' => max(1, $this->toInt($row['duration_days'] ?? '1')),
            'duration_nights' => max(0, $this->toInt($row['duration_nights'] ?? '0')),
            'transport' => $this->normaliseTransport($row['transport'] ?? null),
            'source_transport_raw' => $this->nullableString($row['transport'] ?? null),
            'source_content_status' => $this->nullableString($row['source_content_status'] ?? null),
            'source_ref' => $this->nullableString($row['source_ref'] ?? null),
            'source_url' => $this->nullableString($row['source_url'] ?? null),
            'raw_content_path' => $raw ? 'imports/'.self::PACKAGE_NAME.'/raw/'.basename($raw['raw_content_path'] ?? '') : null,
            'source_conflict' => $this->nullableString($row['source_conflict'] ?? null),
            'source_pricing_note' => $this->nullableString($row['pricing_note'] ?? null),
            'source_default_commission' => $this->nullableNumber($row['default_commission'] ?? null),
            'source_default_seats' => $this->toInt($row['default_seats'] ?? '0') ?: null,
            'starting_price' => $this->nullableNumber($row['starting_price'] ?? null) ?? 0,
            'currency' => strtoupper(substr(trim($row['currency'] ?? 'VND') ?: 'VND', 0, 3)),
            'status' => 'draft',
            'is_active' => false,
            'booking_open' => false,
            'is_featured' => false,
            'seo_title' => $this->nullableString($row['seo_title'] ?? null),
            'seo_description' => $this->nullableString($row['seo_description'] ?? null),
            'published_at' => null,
            'sort_order' => 0,
        ];
    }

    /** @param list<array<string, string>> $rows @return array<string, list<array<string, string>>> */
    private function groupByCode(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $code = trim($row['tour_code'] ?? '');

            if ($code !== '') {
                $grouped[$code][] = $row;
            }
        }

        return $grouped;
    }

    private function excelDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) round((float) $value))->toDateString();
        }

        return Carbon::parse($value)->toDateString();
    }

    private function nullableNumber(?string $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (substr_count($value, '.') > 1 && !str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
        }

        $value = str_replace(',', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normaliseTransport(?string $value): ?string
    {
        $raw = $this->nullableString($value);

        if ($raw === null) {
            return null;
        }

        $transport = preg_replace('/^\s*\d+\s*ngày\s*\/\s*\d+\s*đêm\s*[–-]\s*/iu', '', $raw) ?? $raw;
        $transport = preg_split('/\s+(?:GIÁ|GIÁ TOUR|LỊCH TRÌNH)\b/iu', $transport, 2)[0] ?? $transport;

        return $this->nullableString(mb_substr(trim($transport), 0, 120));
    }

    /** @param array<string, int|string> $activeCategoryIds */
    private function importCategorySlug(array $row, array $activeCategoryIds): string
    {
        $sourceSlug = trim($row['tour_category_slug'] ?? '');

        if ($sourceSlug !== '' && isset($activeCategoryIds[$sourceSlug])) {
            return $sourceSlug;
        }

        return 'tour-kham-pha';
    }

    private function toInt(?string $value): int
    {
        return (int) round((float) str_replace(',', '', trim((string) $value)));
    }

    private function toBool(?string $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y'], true);
    }
}
