<?php

namespace Database\Seeders;

use App\Models\TourCategory;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class TourCategorySeeder extends Seeder
{
    public function run(): void
    {
        $roots = collect([
            ['name' => 'Khám phá', 'slug' => 'tour-kham-pha', 'description' => 'Những hành trình ưu tiên trải nghiệm, khám phá nhịp sống và vẻ đẹp của điểm đến.', 'sort_order' => 1],
            ['name' => 'Nghỉ dưỡng', 'slug' => 'tour-nghi-duong', 'description' => 'Những hành trình ưu tiên thời gian nghỉ ngơi, lưu trú và trải nghiệm thư thái.', 'sort_order' => 2],
            ['name' => 'Gia đình', 'slug' => 'tour-gia-dinh', 'description' => 'Những hành trình có nhịp điệu dễ chịu, điểm tham quan phù hợp cho nhiều thế hệ.', 'sort_order' => 3],
            ['name' => 'Thiên nhiên & mạo hiểm', 'slug' => 'tour-thien-nhien-mao-hiem', 'description' => 'Những hành trình dành cho cung đường, cảnh quan tự nhiên và trải nghiệm giàu năng lượng.', 'sort_order' => 4],
            ['name' => 'Văn hóa & trải nghiệm', 'slug' => 'tour-van-hoa-trai-nghiem', 'description' => 'Những hành trình đi sâu vào lịch sử, văn hóa, ẩm thực và bản sắc địa phương.', 'sort_order' => 5],
            ['name' => 'Team Building', 'slug' => 'tour-team-building', 'description' => 'Những hành trình kết hợp du lịch, hoạt động gắn kết và trải nghiệm dành cho tập thể.', 'sort_order' => 6],
        ])->mapWithKeys(function (array $category): array {
            $model = TourCategory::updateOrCreate(['slug' => $category['slug']], $category + [
                'parent_id' => null,
                'is_active' => true,
                'is_home' => false,
            ]);

            return [$category['slug'] => $model];
        });

        TourCategory::query()
            ->whereIn('slug', [
                'tour-nuoc-ngoai', 'tour-trong-nuoc', 'tour-chau-a', 'tour-chau-au', 'tour-chau-uc',
                'tour-chau-my', 'tour-chau-phi', 'tour-nhat-ban', 'tour-han-quoc', 'tour-mien-bac',
                'tour-mien-trung', 'tour-mien-nam', 'tour-mien-tay', 'tour-uu-dai', 'tour-theo-yeu-cau',
            ])
            ->update(['parent_id' => null, 'is_active' => false, 'is_home' => false]);

        $legacyCategoryIds = TourCategory::query()
            ->whereIn('slug', [
                'tour-nuoc-ngoai', 'tour-trong-nuoc', 'tour-chau-a', 'tour-chau-au', 'tour-chau-uc',
                'tour-chau-my', 'tour-chau-phi', 'tour-nhat-ban', 'tour-han-quoc', 'tour-mien-bac',
                'tour-mien-trung', 'tour-mien-nam', 'tour-mien-tay', 'tour-uu-dai', 'tour-theo-yeu-cau',
            ])
            ->pluck('id');
        $activeCategoryIds = $roots->pluck('id');
        $defaultCategoryId = $roots['tour-kham-pha']->id;

        Tour::query()
            ->whereHas('categories', fn ($query) => $query->whereIn('tour_categories.id', $legacyCategoryIds))
            ->whereDoesntHave('categories', fn ($query) => $query->whereIn('tour_categories.id', $activeCategoryIds))
            ->each(function (Tour $tour) use ($defaultCategoryId): void {
                $tour->categories()->syncWithoutDetaching([$defaultCategoryId => ['sort_order' => 1]]);
            });
    }
}
