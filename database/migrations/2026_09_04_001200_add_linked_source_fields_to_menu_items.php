<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('linked_source_id')->nullable()->after('parent_id');
            $table->string('linked_source_type', 64)->nullable()->after('linked_source_id');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropColumn(['linked_source_id', 'linked_source_type']);
        });
    }
};
