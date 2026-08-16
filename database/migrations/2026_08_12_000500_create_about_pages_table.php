<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('hero_title');
            $table->text('hero_intro')->nullable();
            $table->string('letter_title')->nullable();
            $table->longText('letter_content')->nullable();
            $table->string('story_title')->nullable();
            $table->longText('story_content')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->json('core_values')->nullable();
            $table->json('markets')->nullable();
            $table->json('commitments')->nullable();
            $table->json('audiences')->nullable();
            $table->string('ceo_name')->nullable();
            $table->text('ceo_bio')->nullable();
            $table->string('deputy_name')->nullable();
            $table->text('deputy_bio')->nullable();
            $table->string('background_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('about_pages'); }
};
