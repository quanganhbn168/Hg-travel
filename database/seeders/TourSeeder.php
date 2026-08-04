<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'HG-VN-001', 'name' => 'Đà Nẵng · Hội An 4 ngày 3 đêm', 'slug' => 'da-nang-hoi-an-4-ngay', 'category' => 'tour-mien-trung', 'destination' => 'da-nang', 'price' => 7490000, 'days' => 4, 'nights' => 3, 'featured' => true],
            ['code' => 'HG-VN-002', 'name' => 'Hà Giang mùa đẹp nhất 4 ngày 3 đêm', 'slug' => 'ha-giang-mua-dep-nhat-4-ngay', 'category' => 'tour-mien-bac', 'destination' => 'ha-giang', 'price' => 6890000, 'days' => 4, 'nights' => 3, 'featured' => true],
            ['code' => 'HG-VN-003', 'name' => 'Sa Pa · Fansipan 4 ngày 3 đêm', 'slug' => 'sa-pa-fansipan-4-ngay', 'category' => 'tour-mien-bac', 'destination' => 'sa-pa', 'price' => 7490000, 'days' => 4, 'nights' => 3, 'featured' => false],
            ['code' => 'HG-VN-004', 'name' => 'Hạ Long nghỉ dưỡng du thuyền 3 ngày 2 đêm', 'slug' => 'ha-long-du-thuyen-3-ngay', 'category' => 'tour-mien-bac', 'destination' => 'ha-long', 'price' => 5990000, 'days' => 3, 'nights' => 2, 'featured' => true],
            ['code' => 'HG-VN-005', 'name' => 'Quy Nhơn · Phú Yên 4 ngày 3 đêm', 'slug' => 'quy-nhon-phu-yen-4-ngay', 'category' => 'tour-mien-trung', 'destination' => 'quy-nhon', 'price' => 8290000, 'days' => 4, 'nights' => 3, 'featured' => false],
            ['code' => 'HG-VN-006', 'name' => 'Phú Quốc nghỉ dưỡng 4 ngày 3 đêm', 'slug' => 'phu-quoc-nghi-duong-4-ngay', 'category' => 'tour-mien-nam', 'destination' => 'phu-quoc', 'price' => 9290000, 'days' => 4, 'nights' => 3, 'featured' => true],
            ['code' => 'HG-VN-007', 'name' => 'Cần Thơ · Chợ nổi Cái Răng 3 ngày 2 đêm', 'slug' => 'can-tho-cho-noi-3-ngay', 'category' => 'tour-mien-tay', 'destination' => 'can-tho', 'price' => 4890000, 'days' => 3, 'nights' => 2, 'featured' => false],
            ['code' => 'HG-JP-001', 'name' => 'Tokyo · Fuji · Kyoto 6 ngày 5 đêm', 'slug' => 'tokyo-fuji-kyoto-6-ngay', 'category' => 'tour-chau-a', 'destination' => 'tokyo', 'price' => 32900000, 'days' => 6, 'nights' => 5, 'featured' => true],
            ['code' => 'HG-JP-002', 'name' => 'Hokkaido mùa hè 5 ngày 4 đêm', 'slug' => 'hokkaido-mua-he-5-ngay', 'category' => 'tour-chau-a', 'destination' => 'hokkaido', 'price' => 28900000, 'days' => 5, 'nights' => 4, 'featured' => true],
            ['code' => 'HG-KR-001', 'name' => 'Seoul · Nami · Everland 5 ngày 4 đêm', 'slug' => 'seoul-nami-everland-5-ngay', 'category' => 'tour-chau-a', 'destination' => 'seoul', 'price' => 17900000, 'days' => 5, 'nights' => 4, 'featured' => true],
            ['code' => 'HG-KR-002', 'name' => 'Jeju xanh mát 4 ngày 3 đêm', 'slug' => 'jeju-xanh-mat-4-ngay', 'category' => 'tour-chau-a', 'destination' => 'jeju', 'price' => 15900000, 'days' => 4, 'nights' => 3, 'featured' => false],
            ['code' => 'HG-TH-001', 'name' => 'Bangkok · Pattaya 5 ngày 4 đêm', 'slug' => 'bangkok-pattaya-5-ngay', 'category' => 'tour-chau-a', 'destination' => 'bangkok', 'price' => 13900000, 'days' => 5, 'nights' => 4, 'featured' => true],
            ['code' => 'HG-EU-001', 'name' => 'Pháp · Bỉ · Hà Lan 9 ngày 8 đêm', 'slug' => 'phap-bi-ha-lan-9-ngay', 'category' => 'tour-chau-au', 'destination' => 'paris', 'price' => 69900000, 'days' => 9, 'nights' => 8, 'featured' => true],
            ['code' => 'HG-EU-002', 'name' => 'Ý · Vatican · Rome 7 ngày 6 đêm', 'slug' => 'y-vatican-rome-7-ngay', 'category' => 'tour-chau-au', 'destination' => 'rome', 'price' => 54900000, 'days' => 7, 'nights' => 6, 'featured' => false],
            ['code' => 'HG-AU-001', 'name' => 'Sydney · Melbourne 7 ngày 6 đêm', 'slug' => 'sydney-melbourne-7-ngay', 'category' => 'tour-chau-uc', 'destination' => 'sydney', 'price' => 62900000, 'days' => 7, 'nights' => 6, 'featured' => true],
            ['code' => 'HG-CA-001', 'name' => 'Canada mùa lá đỏ 8 ngày 7 đêm', 'slug' => 'canada-mua-la-do-8-ngay', 'category' => 'tour-chau-my', 'destination' => 'toronto', 'price' => 89900000, 'days' => 8, 'nights' => 7, 'featured' => false],
            ['code' => 'HG-EG-001', 'name' => 'Ai Cập huyền thoại 8 ngày 7 đêm', 'slug' => 'ai-cap-huyen-thoai-8-ngay', 'category' => 'tour-chau-phi', 'destination' => 'cairo', 'price' => 72900000, 'days' => 8, 'nights' => 7, 'featured' => false],
            ['code' => 'HG-SEA-001', 'name' => 'Singapore trọn trải nghiệm 4 ngày 3 đêm', 'slug' => 'singapore-tron-trai-nghiem-4-ngay', 'category' => 'tour-chau-a', 'destination' => 'singapore', 'price' => 14900000, 'days' => 4, 'nights' => 3, 'featured' => true],
            ['code' => 'HG-SEA-002', 'name' => 'Bali nghỉ dưỡng 5 ngày 4 đêm', 'slug' => 'bali-nghi-duong-5-ngay', 'category' => 'tour-chau-a', 'destination' => 'bali', 'price' => 21900000, 'days' => 5, 'nights' => 4, 'featured' => false],
        ] as $index => $data) {
            Tour::updateOrCreate(['slug' => $data['slug']], [
                'tour_category_id' => TourCategory::where('slug', $data['category'])->value('id'),
                'destination_id' => Destination::where('slug', $data['destination'])->value('id'),
                'code' => $data['code'],
                'name' => $data['name'],
                'summary' => 'Lịch trình được HG tuyển chọn, tư vấn rõ ràng và đồng hành xuyên suốt hành trình.',
                'description' => 'Chương trình được thiết kế cân bằng giữa tham quan, trải nghiệm địa phương và thời gian nghỉ ngơi. Liên hệ HG để nhận lịch khởi hành và tư vấn chi tiết.',
                'duration_days' => $data['days'],
                'duration_nights' => $data['nights'],
                'starting_price' => $data['price'],
                'currency' => 'VND',
                'max_guests' => 25,
                'status' => 'published',
                'is_featured' => $data['featured'],
                'is_active' => true,
                'booking_open' => true,
                'published_at' => now()->subDays($index + 1),
                'sort_order' => $index + 1,
            ]);
        }
    }
}
