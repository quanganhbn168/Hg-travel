<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->unsignedSmallInteger('seats_total')->default(0);
            $table->unsignedSmallInteger('seats_reserved')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->string('status', 30)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tour_id', 'departure_date']);
            $table->index(['status', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_schedules');
    }
};
