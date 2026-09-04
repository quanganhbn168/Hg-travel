<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->string('type', 30)->default('city')->after('parent_id');
            $table->string('market', 30)->default('international')->after('type');
            $table->boolean('landing_enabled')->default(false)->after('is_featured');
            $table->index(['market', 'type', 'is_active']);
        });

        $destinations = DB::table('destinations')
            ->get(['id', 'parent_id', 'slug', 'is_system'])
            ->keyBy('id');
        $continentSlugs = ['chau-a', 'chau-au', 'chau-uc', 'chau-my', 'chau-phi'];
        $regionSlugs = ['mien-bac', 'mien-trung', 'mien-nam', 'mien-tay'];

        foreach ($destinations as $destination) {
            $root = $destination;
            $guard = 0;

            while ($root->parent_id && isset($destinations[$root->parent_id]) && $guard++ < 12) {
                $root = $destinations[$root->parent_id];
            }

            $type = match (true) {
                in_array($destination->slug, $continentSlugs, true) => 'continent',
                in_array($destination->slug, $regionSlugs, true) => 'region',
                $destination->slug === 'viet-nam' => 'country',
                (bool) $destination->is_system => 'country',
                default => 'city',
            };

            DB::table('destinations')
                ->where('id', $destination->id)
                ->update([
                    'type' => $type,
                    'market' => $root->slug === 'viet-nam' ? 'domestic' : 'international',
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropIndex(['market', 'type', 'is_active']);
            $table->dropColumn(['type', 'market', 'landing_enabled']);
        });
    }
};
