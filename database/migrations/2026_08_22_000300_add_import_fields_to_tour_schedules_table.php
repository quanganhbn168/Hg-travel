<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_schedules', function (Blueprint $table): void {
            $table->string('transport', 120)->nullable()->after('price');
            $table->string('source_seat_info_raw', 60)->nullable()->after('transport');
            $table->string('source_seat_interpretation', 100)->nullable()->after('source_seat_info_raw');
            $table->string('source_sheet', 120)->nullable()->after('source_seat_interpretation');
            $table->unsignedInteger('source_row')->nullable()->after('source_sheet');
            $table->string('source_date_raw', 60)->nullable()->after('source_row');
            $table->string('source_name_raw')->nullable()->after('source_date_raw');
        });
    }

    public function down(): void
    {
        Schema::table('tour_schedules', function (Blueprint $table): void {
            $table->dropColumn([
                'transport',
                'source_seat_info_raw',
                'source_seat_interpretation',
                'source_sheet',
                'source_row',
                'source_date_raw',
                'source_name_raw',
            ]);
        });
    }
};
