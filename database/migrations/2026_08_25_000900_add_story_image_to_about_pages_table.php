<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->string('story_image')->nullable()->after('hero_image');
        });

        DB::table('about_pages')
            ->whereNull('story_image')
            ->update(['story_image' => 'images/about/hg-trip/journey-kazakhstan.jpg']);
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->dropColumn('story_image');
        });
    }
};
