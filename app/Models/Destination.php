<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Destination extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes;
    public const FIXED_SLUGS = [
        'viet-nam', 'chau-a', 'chau-au', 'chau-uc', 'chau-my', 'chau-phi',
        'mien-bac', 'mien-trung', 'mien-nam', 'mien-tay',
    ];

    protected $fillable = ['parent_id', 'type', 'market', 'name', 'slug', 'summary', 'description', 'cover_image', 'latitude', 'longitude', 'seo_title', 'seo_description', 'sort_order', 'is_featured', 'is_active', 'is_system', 'landing_enabled'];
    protected function casts(): array { return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'is_system' => 'boolean', 'landing_enabled' => 'boolean']; }
    public function scopeSystem($query) { return $query->where('is_system', true); }
    public function scopeEditable($query) { return $query->where('is_system', false); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function tours(): BelongsToMany { return $this->belongsToMany(Tour::class, 'destination_tour')->withPivot('sort_order', 'is_primary')->withTimestamps(); }
    public function aliases(): HasMany { return $this->hasMany(DestinationAlias::class); }
    public function registerMediaCollections(): void { $this->addMediaCollection('cover')->singleFile()->useDisk('public_media'); }
}
