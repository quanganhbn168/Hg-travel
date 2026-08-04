<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourItinerary extends Model
{
    use HasFactory;
    protected $fillable = ['tour_id', 'day_number', 'title', 'description', 'meals', 'accommodation', 'sort_order'];
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
}
