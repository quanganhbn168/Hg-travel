<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'name', 'discount_type', 'discount_value', 'minimum_booking_amount', 'maximum_discount_amount', 'usage_limit', 'used_count', 'starts_at', 'ends_at', 'is_active'];
    protected function casts(): array { return ['discount_value' => 'decimal:2', 'minimum_booking_amount' => 'decimal:2', 'maximum_discount_amount' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean']; }
    public function tours(): BelongsToMany { return $this->belongsToMany(Tour::class); }
}
