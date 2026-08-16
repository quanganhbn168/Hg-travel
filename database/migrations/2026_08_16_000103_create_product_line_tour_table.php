<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_line_tour', function (Blueprint $table): void {
            $table->foreignId('product_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->primary(['product_line_id', 'tour_id']);
            $table->index(['tour_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_line_tour');
    }
};
