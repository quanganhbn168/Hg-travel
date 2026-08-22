<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_GEOGRAPHIC_SLUGS = [
        'tour-nuoc-ngoai',
        'tour-trong-nuoc',
        'tour-chau-a',
        'tour-chau-au',
        'tour-chau-uc',
        'tour-chau-my',
        'tour-chau-phi',
        'tour-nhat-ban',
        'tour-han-quoc',
        'tour-mien-bac',
        'tour-mien-trung',
        'tour-mien-nam',
        'tour-mien-tay',
    ];

    public function up(): void
    {
        Schema::create('tour_category_reframe_backup', function (Blueprint $table): void {
            $table->unsignedBigInteger('tour_id')->primary();
            $table->unsignedBigInteger('old_category_id')->nullable();
        });

        DB::transaction(function (): void {
            $legacyIds = DB::table('tour_categories')
                ->whereIn('slug', self::LEGACY_GEOGRAPHIC_SLUGS)
                ->pluck('id')
                ->all();

            DB::table('tours')
                ->whereIn('tour_category_id', $legacyIds)
                ->get(['id', 'tour_category_id'])
                ->each(function (object $tour): void {
                    DB::table('tour_category_reframe_backup')->insert([
                        'tour_id' => $tour->id,
                        'old_category_id' => $tour->tour_category_id,
                    ]);
                });

            $exploreId = $this->ensureCategory('tour-kham-pha', 'Khám phá', 'Những hành trình tập trung vào trải nghiệm, văn hóa và khám phá điểm đến.', 1);
            $relaxId = $this->ensureCategory('tour-nghi-duong', 'Nghỉ dưỡng', 'Những hành trình ưu tiên thời gian nghỉ ngơi, lưu trú và trải nghiệm thư thái.', 2);
            $this->ensureCategory('tour-uu-dai', 'Ưu đãi', 'Những hành trình đang có chương trình và chính sách ưu đãi.', 3);
            $this->ensureCategory('tour-theo-yeu-cau', 'Theo yêu cầu', 'Những hành trình được thiết kế theo nhu cầu riêng.', 4);

            DB::table('tours')
                ->whereIn('tour_category_id', $legacyIds)
                ->update(['tour_category_id' => $exploreId]);

            DB::table('tours')
                ->where('tour_category_id', $exploreId)
                ->where(function ($query): void {
                    $query
                        ->where('name', 'like', '%nghỉ dưỡng%')
                        ->orWhere('name', 'like', '%du thuyền%');
                })
                ->update(['tour_category_id' => $relaxId]);

            DB::table('tour_categories')
                ->whereIn('slug', self::LEGACY_GEOGRAPHIC_SLUGS)
                ->update([
                    'parent_id' => null,
                    'is_active' => false,
                    'is_home' => false,
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            if (Schema::hasTable('tour_category_reframe_backup')) {
                DB::table('tour_category_reframe_backup')
                    ->orderBy('tour_id')
                    ->get()
                    ->each(function (object $backup): void {
                        DB::table('tours')
                            ->where('id', $backup->tour_id)
                            ->update(['tour_category_id' => $backup->old_category_id]);
                    });

                Schema::dropIfExists('tour_category_reframe_backup');
            }

            DB::table('tour_categories')
                ->whereIn('slug', self::LEGACY_GEOGRAPHIC_SLUGS)
                ->update(['is_active' => true, 'updated_at' => now()]);

            $categoryId = fn (string $slug): ?int => DB::table('tour_categories')->where('slug', $slug)->value('id');
            $internationalId = $categoryId('tour-nuoc-ngoai');
            $domesticId = $categoryId('tour-trong-nuoc');

            if ($internationalId) {
                DB::table('tour_categories')->whereIn('slug', ['tour-chau-a', 'tour-chau-au', 'tour-chau-uc', 'tour-chau-my', 'tour-chau-phi', 'tour-nhat-ban', 'tour-han-quoc'])->update(['parent_id' => $internationalId]);
            }

            if ($domesticId) {
                DB::table('tour_categories')->whereIn('slug', ['tour-mien-bac', 'tour-mien-trung', 'tour-mien-nam', 'tour-mien-tay'])->update(['parent_id' => $domesticId]);
            }

            if ($newExploreId = $categoryId('tour-kham-pha')) {
                $hasTours = DB::table('tours')->where('tour_category_id', $newExploreId)->exists();

                if (! $hasTours) {
                    DB::table('slugs')->where('sluggable_type', 'App\\Models\\TourCategory')->where('sluggable_id', $newExploreId)->delete();
                    DB::table('tour_categories')->where('id', $newExploreId)->delete();
                }
            }
        });
    }

    private function ensureCategory(string $slug, string $name, string $description, int $sortOrder): int
    {
        $categoryId = DB::table('tour_categories')->where('slug', $slug)->value('id');
        $values = [
            'parent_id' => null,
            'name' => $name,
            'description' => $description,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'is_home' => false,
            'updated_at' => now(),
        ];

        if ($categoryId) {
            DB::table('tour_categories')->where('id', $categoryId)->update($values);
        } else {
            $categoryId = DB::table('tour_categories')->insertGetId($values + [
                'slug' => $slug,
                'created_at' => now(),
            ]);
        }

        if (Schema::hasTable('slugs')) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug, 'locale' => config('app.locale', 'vi'), 'sluggable_type' => 'App\\Models\\TourCategory', 'sluggable_id' => $categoryId],
                ['updated_at' => now(), 'created_at' => now()],
            );
        }

        return (int) $categoryId;
    }
};
