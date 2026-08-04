<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        PostCategory::updateOrCreate(['slug' => 'cam-nang-du-lich'], [
            'name' => 'Cẩm nang du lịch',
            'description' => 'Gợi ý hữu ích cho mỗi chuyến đi.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        PostCategory::updateOrCreate(['slug' => 'trai-nghiem'], [
            'name' => 'Trải nghiệm',
            'description' => 'Câu chuyện và cảm hứng từ những hành trình.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        PostCategory::updateOrCreate(['slug' => 'visa-thu-tuc'], [
            'name' => 'Visa & thủ tục',
            'description' => 'Thông tin chuẩn bị hồ sơ, visa và giấy tờ cần thiết.',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        PostCategory::updateOrCreate(['slug' => 'diem-den'], [
            'name' => 'Điểm đến',
            'description' => 'Gợi ý khám phá các điểm đến trong và ngoài nước.',
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }
}
