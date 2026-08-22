<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_category_tour', function (Blueprint $table): void {
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_category_id')->constrained('tour_categories')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->primary(['tour_id', 'tour_category_id']);
            $table->index(['tour_category_id', 'sort_order']);
        });

        DB::table('tours')
            ->whereNotNull('tour_category_id')
            ->orderBy('id')
            ->select(['id', 'tour_category_id'])
            ->each(function (object $tour): void {
                DB::table('tour_category_tour')->insert([
                    'tour_id' => $tour->id,
                    'tour_category_id' => $tour->tour_category_id,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('tours', function (Blueprint $table): void {
            $table->dropForeign(['tour_category_id']);
            $table->dropIndex(['tour_category_id', 'is_active']);
            $table->dropColumn('tour_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->foreignId('tour_category_id')->nullable()->constrained('tour_categories')->nullOnDelete();
            $table->index(['tour_category_id', 'is_active']);
        });

        DB::table('tour_category_tour')
            ->orderBy('tour_id')
            ->orderBy('sort_order')
            ->orderBy('tour_category_id')
            ->get(['tour_id', 'tour_category_id'])
            ->each(function (object $pivot): void {
                DB::table('tours')
                    ->where('id', $pivot->tour_id)
                    ->whereNull('tour_category_id')
                    ->update(['tour_category_id' => $pivot->tour_category_id]);
            });

        Schema::dropIfExists('tour_category_tour');
    }
};
