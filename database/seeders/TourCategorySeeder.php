<?php

namespace Database\Seeders;

use App\Models\TourCategory;
use Illuminate\Database\Seeder;

class TourCategorySeeder extends Seeder
{
    public function run(): void
    {
        $roots = collect([
            ['name' => 'Tour nước ngoài', 'slug' => 'tour-nuoc-ngoai', 'description' => 'Các hành trình quốc tế theo thị trường và điểm đến.', 'sort_order' => 1],
            ['name' => 'Tour trong nước', 'slug' => 'tour-trong-nuoc', 'description' => 'Các hành trình trong nước theo vùng miền và điểm đến.', 'sort_order' => 2],
            ['name' => 'Tour ưu đãi', 'slug' => 'tour-uu-dai', 'description' => 'Các hành trình đang có chương trình ưu đãi.', 'sort_order' => 3],
            ['name' => 'Tour theo yêu cầu', 'slug' => 'tour-theo-yeu-cau', 'description' => 'Hành trình được thiết kế theo nhu cầu riêng.', 'sort_order' => 4],
        ])->mapWithKeys(function (array $category): array {
            $model = TourCategory::updateOrCreate(['slug' => $category['slug']], $category + [
                'parent_id' => null,
                'is_active' => true,
                'is_home' => false,
            ]);

            return [$category['slug'] => $model];
        });

        foreach ([
            ['name' => 'Châu Á', 'slug' => 'tour-chau-a', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Châu Âu', 'slug' => 'tour-chau-au', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Châu Úc', 'slug' => 'tour-chau-uc', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Châu Mỹ', 'slug' => 'tour-chau-my', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Châu Phi', 'slug' => 'tour-chau-phi', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Miền Bắc', 'slug' => 'tour-mien-bac', 'parent' => 'tour-trong-nuoc'],
            ['name' => 'Miền Trung', 'slug' => 'tour-mien-trung', 'parent' => 'tour-trong-nuoc'],
            ['name' => 'Miền Nam', 'slug' => 'tour-mien-nam', 'parent' => 'tour-trong-nuoc'],
            ['name' => 'Miền Tây', 'slug' => 'tour-mien-tay', 'parent' => 'tour-trong-nuoc'],
            ['name' => 'Tour Nhật Bản', 'slug' => 'tour-nhat-ban', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Tour Hàn Quốc', 'slug' => 'tour-han-quoc', 'parent' => 'tour-nuoc-ngoai'],
            ['name' => 'Tour nghỉ dưỡng', 'slug' => 'tour-nghi-duong', 'parent' => 'tour-trong-nuoc'],
        ] as $index => $category) {
            TourCategory::updateOrCreate(['slug' => $category['slug']], [
                'parent_id' => $roots[$category['parent']]->id,
                'name' => $category['name'],
                'description' => 'Danh mục tour '.$category['name'].'.',
                'sort_order' => $index + 1,
                'is_active' => true,
                'is_home' => false,
            ]);
        }
    }
}
