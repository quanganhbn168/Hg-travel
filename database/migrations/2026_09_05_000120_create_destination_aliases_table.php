<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('normalized_alias', 255);
            $table->string('locale', 12)->default('vi');
            $table->string('source', 100)->nullable();
            $table->timestamps();
            $table->unique(['normalized_alias', 'locale']);
            $table->index(['destination_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_aliases');
    }
};
