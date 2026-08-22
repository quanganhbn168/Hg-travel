<?php

namespace Tests\Unit;

use App\Support\TourScheduleSeatInfo;
use PHPUnit\Framework\TestCase;

class TourScheduleSeatInfoTest extends TestCase
{
    public function test_it_reads_hg_trip_booked_and_total_seats(): void
    {
        $seatInfo = TourScheduleSeatInfo::parseBookedTotal('2/25');

        $this->assertSame(25, $seatInfo['seats_total']);
        $this->assertSame(2, $seatInfo['seats_reserved']);
        $this->assertSame('Đã đặt/Tổng số chỗ (x/y)', $seatInfo['interpretation']);
    }

    public function test_it_accepts_a_trailing_spreadsheet_apostrophe(): void
    {
        $seatInfo = TourScheduleSeatInfo::parseBookedTotal("20/26'");

        $this->assertSame(26, $seatInfo['seats_total']);
        $this->assertSame(20, $seatInfo['seats_reserved']);
    }

    public function test_it_rejects_a_non_fractional_seat_value(): void
    {
        $this->assertNull(TourScheduleSeatInfo::parseBookedTotal('25'));
    }
}
