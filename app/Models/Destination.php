<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Destination extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes;
    protected $fillable = ['parent_id', 'name', 'slug', 'summary', 'description', 'cover_image', 'latitude', 'longitude', 'seo_title', 'seo_description', 'sort_order', 'is_featured', 'is_active'];
    protected function casts(): array { return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_featured' => 'boolean', 'is_active' => 'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function tours(): HasMany { return $this->hasMany(Tour::class); }
    public function registerMediaCollections(): void { $this->addMediaCollection('cover')->singleFile()->useDisk('public_media'); }
}
