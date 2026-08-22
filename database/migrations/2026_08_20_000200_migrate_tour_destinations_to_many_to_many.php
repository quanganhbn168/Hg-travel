<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_tour', function (Blueprint $table): void {
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->primary(['tour_id', 'destination_id']);
            $table->index(['destination_id', 'sort_order']);
        });

        DB::table('tours')
            ->whereNotNull('destination_id')
            ->orderBy('id')
            ->select(['id', 'destination_id'])
            ->each(function (object $tour): void {
                DB::table('destination_tour')->insert([
                    'tour_id' => $tour->id,
                    'destination_id' => $tour->destination_id,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('tours', function (Blueprint $table): void {
            $table->dropForeign(['destination_id']);
            $table->dropIndex(['destination_id', 'is_active']);
            $table->dropColumn('destination_id');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['destination_id', 'is_active']);
        });

        DB::table('destination_tour')
            ->orderBy('tour_id')
            ->orderBy('sort_order')
            ->orderBy('destination_id')
            ->get(['tour_id', 'destination_id'])
            ->each(function (object $pivot): void {
                DB::table('tours')
                    ->where('id', $pivot->tour_id)
                    ->whereNull('destination_id')
                    ->update(['destination_id' => $pivot->destination_id]);
            });

        Schema::dropIfExists('destination_tour');
    }
};
