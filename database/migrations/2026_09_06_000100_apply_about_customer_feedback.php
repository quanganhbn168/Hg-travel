<?php

use App\Models\AboutPage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = AboutPage::query()->where('key', 'about')->first();

        if (! $page) {
            return;
        }

        $content = $page->profile_content;
        if (data_get($content, 'organisation.eyebrow') === 'BỘ MÁY VÀ HIỆN DIỆ') {
            data_set($content, 'organisation.eyebrow', 'BỘ MÁY VÀ HIỆN DIỆN');
        }

        $page->update([
            'profile_content' => $content,
            'background_image' => 'images/about/hg-trip/hg-trip-traveller.png',
            'story_image' => 'images/about/hg-trip/hg-trip-south-africa.jpg',
        ]);
    }

    public function down(): void
    {
        // Retain customer content and any subsequent CMS edits on rollback.
    }
};
