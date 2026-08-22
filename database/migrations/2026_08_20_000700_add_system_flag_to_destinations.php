<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->boolean('is_system')->default(false)->after('is_active');
            $table->index(['is_system', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropIndex(['is_system', 'parent_id']);
            $table->dropColumn('is_system');
        });
    }
};
