<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductLine extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'kicker', 'summary', 'description', 'icon', 'cover_image', 'hero_image',
        'benefits', 'seo_title', 'seo_description', 'sort_order', 'is_active', 'is_home',
    ];

    protected function casts(): array
    {
        return ['benefits' => 'array', 'is_active' => 'boolean', 'is_home' => 'boolean'];
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class)->withPivot('sort_order')->withTimestamps();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withPivot('sort_order')->withTimestamps();
    }
}
