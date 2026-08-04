<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SliderItem extends Model
{
    protected $fillable = ['slider_id', 'title', 'subtitle', 'image_path', 'button_label', 'button_url', 'sort_order', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function slider(): BelongsTo { return $this->belongsTo(Slider::class); }
}
