<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourItinerary;
use Illuminate\Database\Seeder;

class TourItinerarySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tour::query()->where('status', 'published')->with('destinations')->get() as $tour) {
            $totalDays = max(1, (int) $tour->duration_days);

            foreach (range(1, $totalDays) as $day) {
                $isFirstDay = $day === 1;
                $isLastDay = $day === $totalDays;

                TourItinerary::updateOrCreate(['tour_id' => $tour->id, 'day_number' => $day], [
                    'title' => $isFirstDay
                        ? 'Khởi hành · nhận phòng'
                        : ($isLastDay ? 'Tạm biệt hành trình · trở về' : 'Khám phá '.($tour->destinations->pluck('name')->first() ?: 'điểm đến')),
                    'description' => $isFirstDay
                        ? 'Đoàn tập trung, di chuyển theo chương trình, nhận phòng và nghỉ ngơi trước hoạt động buổi tối.'
                        : ($isLastDay
                            ? 'Dùng bữa sáng, hoàn tất các trải nghiệm còn lại và trở về theo lịch bay hoặc lịch xe đã sắp xếp.'
                            : 'Tham quan các điểm nổi bật theo chương trình, xen kẽ thời gian trải nghiệm địa phương và nghỉ ngơi.'),
                    'meals' => $isFirstDay ? 'Trưa · Tối' : ($isLastDay ? 'Sáng' : 'Sáng · Trưa · Tối'),
                    'accommodation' => $isLastDay ? null : 'Khách sạn tiêu chuẩn 4 sao',
                    'sort_order' => $day,
                ]);
            }

            $tour->itineraries()->where('day_number', '>', $totalDays)->delete();
        }
    }
}
