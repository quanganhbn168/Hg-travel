<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourReview extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['tour_id', 'user_id', 'booking_id', 'rating', 'title', 'content', 'status'];
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}
