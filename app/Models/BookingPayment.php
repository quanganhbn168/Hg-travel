<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingPayment extends Model
{
    use HasFactory;
    protected $fillable = ['booking_id', 'amount', 'method', 'status', 'transaction_reference', 'paid_at', 'metadata'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'metadata' => 'array']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
}
