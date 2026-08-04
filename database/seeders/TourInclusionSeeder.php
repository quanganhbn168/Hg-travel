<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourInclusion;
use Illuminate\Database\Seeder;

class TourInclusionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'included' => [
                'Vé máy bay hoặc phương tiện di chuyển theo chương trình',
                'Khách sạn tiêu chuẩn theo lịch trình',
                'Xe đưa đón và hướng dẫn viên đồng hành',
                'Các bữa ăn, vé tham quan được nêu trong chương trình',
                'Bảo hiểm du lịch theo quy định của tour',
            ],
            'excluded' => [
                'Chi phí cá nhân, đồ uống và các dịch vụ ngoài chương trình',
                'Tiền bồi dưỡng cho hướng dẫn viên và tài xế (nếu có)',
                'Chi phí làm visa, hộ chiếu hoặc phụ thu phòng đơn (nếu phát sinh)',
            ],
        ];

        foreach (Tour::query()->where('status', 'published')->get() as $tour) {
            foreach ($items as $type => $contents) {
                foreach ($contents as $sort => $content) {
                    TourInclusion::updateOrCreate([
                        'tour_id' => $tour->id,
                        'type' => $type,
                        'sort_order' => $sort + 1,
                    ], ['content' => $content]);
                }
            }
        }
    }
}
