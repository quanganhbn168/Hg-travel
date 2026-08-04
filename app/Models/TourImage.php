<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourImage extends Model
{
    use HasFactory;
    protected $fillable = ['tour_id', 'path', 'alt_text', 'is_cover', 'sort_order'];
    protected function casts(): array { return ['is_cover' => 'boolean']; }
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
}
