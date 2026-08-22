<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_itineraries', function (Blueprint $table): void {
            $table->text('title')->change();
            $table->text('meals')->nullable()->change();
            $table->text('accommodation')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tour_itineraries', function (Blueprint $table): void {
            $table->string('title')->change();
            $table->string('meals')->nullable()->change();
            $table->string('accommodation')->nullable()->change();
        });
    }
};
