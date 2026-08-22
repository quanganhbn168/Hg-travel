<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourSection extends Model
{
    protected $fillable = [
        'tour_id',
        'type',
        'title',
        'content',
        'sort_order',
        'source_ref',
        'source_url',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
