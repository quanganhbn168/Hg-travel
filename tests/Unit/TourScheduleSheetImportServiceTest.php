<?php

namespace Tests\Unit;

use App\Services\HgTripXlsxReader;
use App\Services\TourScheduleSheetImportService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TourScheduleSheetImportServiceTest extends TestCase
{
    #[Test]
    public function it_reads_the_monthly_schedule_sheet_after_its_title_rows(): void
    {
        $preview = $this->service()->previewRows([
            'Trang tính1' => [
                ['row' => 1, 'cells' => [0 => 'LỊCH KHỞI HÀNH HÀNG THÁNG']],
                ['row' => 12, 'cells' => [
                    0 => 'TÊN TOUR',
                    1 => 'THỜI GIAN',
                    2 => 'PHƯƠNG TIỆN DI CHUYỂN',
                    3 => 'LỊCH KHỞI HÀNH',
                    4 => 'GIÁ TRỌN GÓI',
                    5 => 'COM',
                    6 => 'SỐ CHỖ',
                ]],
                ['row' => 13, 'cells' => [
                    0 => 'Tour mẫu 5N4Đ',
                    1 => '5N4Đ',
                    2 => 'Bay VNA',
                    3 => 'Tháng 10: 13, 28, 30',
                    4 => '23.990K',
                    5 => '800K',
                    6 => '25',
                ]],
                ['row' => 14, 'cells' => [
                    3 => 'Tháng 2/2027: 17',
                    4 => '24.990K',
                    6 => '26',
                ]],
            ],
        ], 2026);

        $source = collect($preview['sources'])->first();

        $this->assertSame('Trang tính1', $preview['sheet_name']);
        $this->assertSame(12, $preview['header_row']);
        $this->assertSame(4, $preview['schedule_count']);
        $this->assertSame('2026-10-13', $source['schedules'][0]['departure_date']);
        $this->assertSame('2026-10-17', $source['schedules'][0]['return_date']);
        $this->assertSame(23990000, $source['schedules'][0]['price']);
        $this->assertSame(25, $source['schedules'][0]['seats_total']);
        $this->assertSame('2027-02-17', $source['schedules'][3]['departure_date']);
        $this->assertSame('2027-02-21', $source['schedules'][3]['return_date']);
    }

    private function service(): TourScheduleSheetImportService
    {
        return new TourScheduleSheetImportService(new HgTripXlsxReader());
    }
}
