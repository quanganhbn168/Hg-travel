<?php

namespace Tests\Unit;

use App\Models\TourSchedule;
use PHPUnit\Framework\TestCase;

class TourScheduleTest extends TestCase
{
    public function test_it_reports_remaining_and_total_seats(): void
    {
        $schedule = new TourSchedule([
            'seats_total' => 25,
            'seats_reserved' => 7,
            'status' => 'open',
        ]);

        $this->assertSame(18, $schedule->seatsLeft());
        $this->assertSame('Còn 18/25 chỗ', $schedule->slotLabel());
        $this->assertTrue($schedule->isAvailable());
    }

    public function test_it_does_not_invent_a_slot_total_when_capacity_is_unknown(): void
    {
        $schedule = new TourSchedule([
            'seats_total' => 0,
            'seats_reserved' => 10,
            'status' => 'open',
        ]);

        $this->assertNull($schedule->seatsLeft());
        $this->assertSame('Đang cập nhật', $schedule->slotLabel());
        $this->assertTrue($schedule->isAvailable());
    }

    public function test_a_full_schedule_is_not_available(): void
    {
        $schedule = new TourSchedule([
            'seats_total' => 25,
            'seats_reserved' => 25,
            'status' => 'open',
        ]);

        $this->assertSame(0, $schedule->seatsLeft());
        $this->assertFalse($schedule->isAvailable());
    }

    public function test_it_uses_the_departure_sale_price_when_available(): void
    {
        $schedule = new TourSchedule([
            'price' => 12_900_000,
            'sale_price' => 10_900_000,
        ]);

        $this->assertSame(10_900_000.0, $schedule->effectivePrice());
    }
}
