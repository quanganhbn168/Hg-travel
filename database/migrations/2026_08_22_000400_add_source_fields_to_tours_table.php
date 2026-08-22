<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->string('source_content_status', 50)->nullable()->after('transport');
            $table->string('source_ref')->nullable()->after('source_content_status');
            $table->text('source_url')->nullable()->after('source_ref');
            $table->string('raw_content_path')->nullable()->after('source_url');
            $table->text('source_conflict')->nullable()->after('raw_content_path');
            $table->text('source_pricing_note')->nullable()->after('source_conflict');
            $table->decimal('source_default_commission', 15, 2)->nullable()->after('source_pricing_note');
            $table->unsignedSmallInteger('source_default_seats')->nullable()->after('source_default_commission');
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table): void {
            $table->dropColumn([
                'source_content_status',
                'source_ref',
                'source_url',
                'raw_content_path',
                'source_conflict',
                'source_pricing_note',
                'source_default_commission',
                'source_default_seats',
            ]);
        });
    }
};
