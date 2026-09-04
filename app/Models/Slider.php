<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slider extends Model
{
    protected $fillable = ['name', 'key', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function items(): HasMany { return $this->hasMany(SliderItem::class)->orderBy('sort_order')->orderBy('id'); }
}
