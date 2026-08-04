<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['booking_payment_id', 'gateway', 'external_id', 'amount', 'status', 'transaction_content', 'transaction_at', 'payload'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'transaction_at' => 'datetime', 'payload' => 'array']; }
    public function bookingPayment(): BelongsTo { return $this->belongsTo(BookingPayment::class); }
}
