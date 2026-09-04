<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destination_tour', function (Blueprint $table): void {
            $table->boolean('is_primary')->default(false)->after('sort_order');
        });

        DB::table('destination_tour')
            ->select(['tour_id', 'destination_id'])
            ->orderBy('tour_id')
            ->orderBy('sort_order')
            ->orderBy('destination_id')
            ->get()
            ->groupBy('tour_id')
            ->each(function ($rows): void {
                $first = $rows->first();

                if ($first) {
                    DB::table('destination_tour')
                        ->where('tour_id', $first->tour_id)
                        ->where('destination_id', $first->destination_id)
                        ->update(['is_primary' => true]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('destination_tour', function (Blueprint $table): void {
            $table->dropColumn('is_primary');
        });
    }
};
