<?php

namespace App\Models;

use App\Traits\HasManagedImages;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourCategory extends Model
{
    use HasFactory, HasSlug, SoftDeletes;
    use HasManagedImages;

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'cover_image', 'seo_title', 'seo_description', 'sort_order', 'is_active', 'is_home'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_home' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_category_tour')->withPivot('sort_order')->withTimestamps()->orderByPivot('sort_order');
    }
}
