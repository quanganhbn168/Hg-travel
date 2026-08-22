<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES = [
        ['slug' => 'tour-kham-pha', 'name' => 'Khám phá', 'description' => 'Những hành trình ưu tiên trải nghiệm, khám phá nhịp sống và vẻ đẹp của điểm đến.', 'sort_order' => 1],
        ['slug' => 'tour-nghi-duong', 'name' => 'Nghỉ dưỡng', 'description' => 'Những hành trình ưu tiên thời gian nghỉ ngơi, lưu trú và trải nghiệm thư thái.', 'sort_order' => 2],
        ['slug' => 'tour-gia-dinh', 'name' => 'Gia đình', 'description' => 'Những hành trình có nhịp điệu dễ chịu, điểm tham quan phù hợp cho nhiều thế hệ.', 'sort_order' => 3],
        ['slug' => 'tour-thien-nhien-mao-hiem', 'name' => 'Thiên nhiên & mạo hiểm', 'description' => 'Những hành trình dành cho cung đường, cảnh quan tự nhiên và trải nghiệm giàu năng lượng.', 'sort_order' => 4],
        ['slug' => 'tour-van-hoa-trai-nghiem', 'name' => 'Văn hóa & trải nghiệm', 'description' => 'Những hành trình đi sâu vào lịch sử, văn hóa, ẩm thực và bản sắc địa phương.', 'sort_order' => 5],
    ];

    private const LEGACY_SLUGS = [
        'tour-nuoc-ngoai', 'tour-trong-nuoc', 'tour-chau-a', 'tour-chau-au', 'tour-chau-uc',
        'tour-chau-my', 'tour-chau-phi', 'tour-nhat-ban', 'tour-han-quoc', 'tour-mien-bac',
        'tour-mien-trung', 'tour-mien-nam', 'tour-mien-tay', 'tour-uu-dai', 'tour-theo-yeu-cau',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $categoryIds = [];

            foreach (self::TYPES as $type) {
                $categoryIds[$type['slug']] = $this->ensureCategory($type);
            }

            DB::table('tour_categories')
                ->whereIn('slug', self::LEGACY_SLUGS)
                ->update([
                    'parent_id' => null,
                    'is_active' => false,
                    'is_home' => false,
                    'updated_at' => now(),
                ]);

            $assignments = [
                'ha-giang-mua-dep-nhat-4-ngay' => 'tour-thien-nhien-mao-hiem',
                'sa-pa-fansipan-4-ngay' => 'tour-thien-nhien-mao-hiem',
                'can-tho-cho-noi-3-ngay' => 'tour-van-hoa-trai-nghiem',
                'tokyo-fuji-kyoto-6-ngay' => 'tour-van-hoa-trai-nghiem',
                'hokkaido-mua-he-5-ngay' => 'tour-thien-nhien-mao-hiem',
                'seoul-nami-everland-5-ngay' => 'tour-gia-dinh',
                'jeju-xanh-mat-4-ngay' => 'tour-nghi-duong',
                'phap-bi-ha-lan-9-ngay' => 'tour-van-hoa-trai-nghiem',
                'y-vatican-rome-7-ngay' => 'tour-van-hoa-trai-nghiem',
                'ai-cap-huyen-thoai-8-ngay' => 'tour-van-hoa-trai-nghiem',
                'singapore-tron-trai-nghiem-4-ngay' => 'tour-gia-dinh',
                'bali-nghi-duong-5-ngay' => 'tour-nghi-duong',
            ];

            foreach ($assignments as $tourSlug => $categorySlug) {
                DB::table('tours')
                    ->where('slug', $tourSlug)
                    ->update(['tour_category_id' => $categoryIds[$categorySlug], 'updated_at' => now()]);
            }

            DB::table('product_lines')
                ->where('slug', 'nghi-duong-cao-cap')
                ->update([
                    'name' => 'Hành trình riêng cao cấp',
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $exploreId = DB::table('tour_categories')->where('slug', 'tour-kham-pha')->value('id');

            if ($exploreId) {
                DB::table('tours')
                    ->whereIn('tour_category_id', DB::table('tour_categories')->whereIn('slug', ['tour-gia-dinh', 'tour-thien-nhien-mao-hiem', 'tour-van-hoa-trai-nghiem'])->pluck('id'))
                    ->update(['tour_category_id' => $exploreId, 'updated_at' => now()]);
            }

            DB::table('tour_categories')
                ->whereIn('slug', ['tour-gia-dinh', 'tour-thien-nhien-mao-hiem', 'tour-van-hoa-trai-nghiem'])
                ->update(['is_active' => false, 'is_home' => false, 'updated_at' => now()]);

            DB::table('tour_categories')
                ->whereIn('slug', ['tour-uu-dai', 'tour-theo-yeu-cau'])
                ->update(['is_active' => true, 'updated_at' => now()]);

            DB::table('product_lines')
                ->where('slug', 'nghi-duong-cao-cap')
                ->update(['name' => 'Nghỉ dưỡng cao cấp', 'updated_at' => now()]);
        });
    }

    private function ensureCategory(array $type): int
    {
        $values = [
            'parent_id' => null,
            'name' => $type['name'],
            'description' => $type['description'],
            'sort_order' => $type['sort_order'],
            'is_active' => true,
            'is_home' => false,
            'updated_at' => now(),
        ];
        $categoryId = DB::table('tour_categories')->where('slug', $type['slug'])->value('id');

        if ($categoryId) {
            DB::table('tour_categories')->where('id', $categoryId)->update($values);
        } else {
            $categoryId = DB::table('tour_categories')->insertGetId($values + [
                'slug' => $type['slug'],
                'created_at' => now(),
            ]);
        }

        if (Schema::hasTable('slugs')) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $type['slug'], 'locale' => config('app.locale', 'vi'), 'sluggable_type' => 'App\\Models\\TourCategory', 'sluggable_id' => $categoryId],
                ['updated_at' => now(), 'created_at' => now()],
            );
        }

        return (int) $categoryId;
    }
};
