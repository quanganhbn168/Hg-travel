<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 50);
            $table->string('external_id')->nullable()->unique();
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('unmatched');
            $table->text('transaction_content')->nullable();
            $table->timestamp('transaction_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
