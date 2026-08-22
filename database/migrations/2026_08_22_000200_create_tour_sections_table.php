<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('other');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_ref')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();

            $table->index(['tour_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_sections');
    }
};
