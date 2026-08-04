<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['tour_id', 'departure_date', 'return_date', 'seats_total', 'seats_reserved', 'price', 'status', 'notes'];
    protected function casts(): array { return ['departure_date' => 'date', 'return_date' => 'date', 'price' => 'decimal:2']; }
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
}
