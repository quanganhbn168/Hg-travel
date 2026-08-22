<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_moments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('travel_moment_groups')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['group_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_moments');
    }
};
