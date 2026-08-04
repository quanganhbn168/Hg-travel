<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Database\Seeder;

class TourScheduleSeeder extends Seeder
{
    private const LEGACY_SAMPLE_NOTE = 'Lịch khởi hành mẫu để quản trị tiếp tục cập nhật.';

    public function run(): void
    {
        TourSchedule::query()->where('notes', self::LEGACY_SAMPLE_NOTE)->delete();

        $firstDeparture = now()->startOfMonth()->addMonth();

        foreach (Tour::query()->where('status', 'published')->orderBy('sort_order')->get() as $tourIndex => $tour) {
            foreach (range(0, 2) as $scheduleIndex) {
                $departure = $firstDeparture->copy()->addDays(($tourIndex * 2) + ($scheduleIndex * 14));
                $seatsTotal = 25;
                $seatsReserved = min($seatsTotal - 2, ($tourIndex + ($scheduleIndex * 4)) % 14);

                TourSchedule::updateOrCreate([
                    'tour_id' => $tour->id,
                    'departure_date' => $departure->toDateString(),
                ], [
                    'return_date' => $departure->copy()->addDays(max(1, (int) $tour->duration_days - 1))->toDateString(),
                    'seats_total' => $seatsTotal,
                    'seats_reserved' => $seatsReserved,
                    'price' => (float) $tour->starting_price + ($scheduleIndex * 300000),
                    'status' => 'open',
                    'notes' => 'Lịch khởi hành mẫu. Vui lòng liên hệ HG để xác nhận chỗ.',
                ]);
            }
        }
    }
}
