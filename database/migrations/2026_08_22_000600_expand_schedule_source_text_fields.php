<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_schedules', function (Blueprint $table): void {
            $table->text('source_seat_info_raw')->nullable()->change();
            $table->text('source_seat_interpretation')->nullable()->change();
            $table->text('source_date_raw')->nullable()->change();
            $table->text('source_name_raw')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tour_schedules', function (Blueprint $table): void {
            $table->string('source_seat_info_raw', 60)->nullable()->change();
            $table->string('source_seat_interpretation', 100)->nullable()->change();
            $table->string('source_date_raw', 60)->nullable()->change();
            $table->string('source_name_raw')->nullable()->change();
        });
    }
};
