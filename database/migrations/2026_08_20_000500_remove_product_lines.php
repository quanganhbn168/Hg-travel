<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('slugs')) {
            DB::table('slugs')
                ->where('sluggable_type', 'App\\Models\\ProductLine')
                ->delete();
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')
                ->where('name', 'like', 'product-lines.%')
                ->delete();
        }

        Schema::dropIfExists('product_line_tour');
        Schema::dropIfExists('product_line_service');
        Schema::dropIfExists('product_lines');
    }

    public function down(): void
    {
        throw new \RuntimeException('The ProductLine/Solutions module was intentionally removed and cannot be restored by rollback.');
    }
};
