<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;
    protected $fillable = ['booking_id', 'tour_id', 'tour_schedule_id', 'tour_name', 'departure_date', 'adults', 'children', 'unit_price', 'total_price', 'traveler_details'];
    protected function casts(): array { return ['departure_date' => 'date', 'unit_price' => 'decimal:2', 'total_price' => 'decimal:2', 'traveler_details' => 'array']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
    public function schedule(): BelongsTo { return $this->belongsTo(TourSchedule::class, 'tour_schedule_id'); }
}
