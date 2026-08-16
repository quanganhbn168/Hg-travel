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

class Tour extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes;
    protected $fillable = ['tour_category_id', 'destination_id', 'code', 'name', 'slug', 'summary', 'description', 'duration_days', 'duration_nights', 'starting_price', 'currency', 'max_guests', 'status', 'is_featured', 'is_active', 'booking_open', 'seo_title', 'seo_description', 'published_at', 'sort_order'];
    protected function casts(): array { return ['starting_price' => 'decimal:2', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'booking_open' => 'boolean', 'published_at' => 'datetime']; }
    public function category(): BelongsTo { return $this->belongsTo(TourCategory::class, 'tour_category_id'); }
    public function destination(): BelongsTo { return $this->belongsTo(Destination::class); }
    public function images(): HasMany { return $this->hasMany(TourImage::class); }
    public function itineraries(): HasMany { return $this->hasMany(TourItinerary::class)->orderBy('day_number'); }
    public function schedules(): HasMany { return $this->hasMany(TourSchedule::class); }
    public function inclusions(): HasMany { return $this->hasMany(TourInclusion::class)->orderBy('sort_order'); }
    public function coupons(): BelongsToMany { return $this->belongsToMany(Coupon::class); }
    public function promotions(): BelongsToMany { return $this->belongsToMany(Promotion::class); }
    public function productLines(): BelongsToMany { return $this->belongsToMany(ProductLine::class)->withPivot('sort_order')->withTimestamps(); }
    public function reviews(): HasMany { return $this->hasMany(TourReview::class); }
    public function registerMediaCollections(): void { $this->addMediaCollection('tour_images')->useDisk('public_media'); }
}
