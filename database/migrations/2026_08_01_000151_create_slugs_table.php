<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slugs', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->string('sluggable_type');
            $table->unsignedBigInteger('sluggable_id');
            $table->string('locale', 10);
            $table->timestamps();
            $table->unique(['slug', 'locale']);
            $table->index(['sluggable_type', 'sluggable_id']);
            $table->index(['sluggable_type', 'sluggable_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slugs');
    }
};
