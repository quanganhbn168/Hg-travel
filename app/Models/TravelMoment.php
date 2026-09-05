<?php

namespace App\Models;

use App\Traits\HasManagedImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelMoment extends Model
{
    use HasFactory;
    use HasManagedImages;

    protected $fillable = ['group_id', 'title', 'slug', 'image_url', 'alt_text', 'caption', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TravelMomentGroup::class, 'group_id');
    }
}
