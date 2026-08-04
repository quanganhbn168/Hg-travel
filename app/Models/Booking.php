<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'booking_code', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'status', 'payment_status', 'payment_method', 'subtotal', 'discount_amount', 'total_amount', 'currency', 'notes', 'booked_at'];
    protected function casts(): array { return ['subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total_amount' => 'decimal:2', 'booked_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(BookingItem::class); }
    public function statusHistories(): HasMany { return $this->hasMany(BookingStatusHistory::class); }
    public function payments(): HasMany { return $this->hasMany(BookingPayment::class); }
    public function reviews(): HasMany { return $this->hasMany(TourReview::class); }
}
