<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'tour_images' => ['media_id'], 'tours' => ['banner_media_id'],
        'destinations' => ['cover_media_id'], 'tour_categories' => ['cover_media_id'],
        'services' => ['cover_media_id'], 'posts' => ['cover_media_id'],
        'testimonials' => ['avatar_media_id'], 'travel_moments' => ['media_id'],
        'slider_items' => ['media_id'],
        'about_pages' => ['hero_media_id', 'background_media_id', 'story_media_id'],
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
                foreach ($columns as $column) {
                    $blueprint->foreignId($column)->nullable()->constrained('media')->restrictOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
                foreach ($columns as $column) {
                    $blueprint->dropConstrainedForeignId($column);
                }
            });
        }
    }
};
