<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_lines', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kicker')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('cover_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('benefits')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_home')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_home', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_lines');
    }
};
