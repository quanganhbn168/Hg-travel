<?php

namespace App\Models;

use App\Traits\HasManagedImages;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tour extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, SoftDeletes;
    use HasManagedImages;

    protected $fillable = ['code', 'name', 'slug', 'summary', 'description', 'banner_image', 'duration_days', 'duration_nights', 'transport', 'source_transport_raw', 'source_content_status', 'source_ref', 'source_url', 'raw_content_path', 'source_conflict', 'source_pricing_note', 'source_default_commission', 'source_default_seats', 'starting_price', 'currency', 'max_guests', 'status', 'is_featured', 'is_active', 'booking_open', 'seo_title', 'seo_description', 'published_at', 'sort_order'];

    protected function casts(): array
    {
        return ['starting_price' => 'decimal:2', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'booking_open' => 'boolean', 'published_at' => 'datetime'];
    }

    /**
     * Display price shared by every tour card: cheapest bookable schedule
     * (sale price if set, else list price), falling back to the tour's
     * starting price and the given promotion.
     *
     * @return array{price: float, sale_price: ?float, discount_percent: ?int}
     */
    public function priceSummary(?Promotion $promotion = null): array
    {
        $schedule = $this->relationLoaded('schedules')
            ? $this->getRelation('schedules')
                ->filter(fn (TourSchedule $schedule): bool => $schedule->isAvailable()
                    && $schedule->departure_date?->gte(today())
                    && $schedule->effectivePrice() > 0)
                ->sortBy(fn (TourSchedule $schedule): float => $schedule->effectivePrice())
                ->first()
            : null;

        if ($schedule) {
            $listPrice = (float) $schedule->price;
            $scheduleSale = (float) $schedule->sale_price;
            $price = $listPrice > 0 ? $listPrice : $scheduleSale;

            if ($scheduleSale > 0 && $scheduleSale < $price) {
                return [
                    'price' => $price,
                    'sale_price' => $scheduleSale,
                    'discount_percent' => min(100, max(1, (int) round((1 - $scheduleSale / $price) * 100))),
                ];
            }
        } else {
            $price = (float) $this->starting_price;
        }

        if (! $promotion || $price <= 0) {
            return ['price' => $price, 'sale_price' => null, 'discount_percent' => null];
        }

        $isPercentage = $promotion->discount_type === 'percentage';
        $value = (float) $promotion->discount_value;
        $percent = $isPercentage ? $value : ($value / $price) * 100;

        return [
            'price' => $price,
            'sale_price' => $isPercentage ? max(0, $price * (1 - $value / 100)) : max(0, $price - $value),
            'discount_percent' => min(100, max(1, (int) round($percent))),
        ];
    }

    /**
     * Bookable schedules (loaded `schedules` relation) shaped for booking triggers.
     *
     * @return list<array{id: int, date: string, iso: string, summary: string}>
     */
    public function scheduleOptions(): array
    {
        if (! $this->relationLoaded('schedules')) {
            return [];
        }

        return $this->getRelation('schedules')
            ->filter(fn (TourSchedule $schedule): bool => $schedule->isAvailable() && $schedule->departure_date?->gte(today()))
            ->sortBy('departure_date')
            ->map(fn (TourSchedule $schedule): array => [
                'id' => $schedule->getKey(),
                'date' => $schedule->departure_date->format('d/m'),
                'iso' => $schedule->departure_date->format('Y-m-d'),
                'summary' => $schedule->departure_date->format('d/m/Y')
                    .($schedule->effectivePrice() > 0 ? ' · '.number_format($schedule->effectivePrice(), 0, ',', '.').'đ' : '')
                    .' · '.$schedule->slotLabel(),
            ])
            ->values()
            ->all();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TourCategory::class, 'tour_category_tour')->withPivot('sort_order')->withTimestamps()->orderByPivot('sort_order');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'destination_tour')->withPivot('sort_order', 'is_primary')->withTimestamps()->orderByPivot('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourItinerary::class)->orderBy('day_number');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TourSchedule::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TourSection::class)->orderBy('sort_order');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(TourInclusion::class)->orderBy('sort_order');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TourReview::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('tour_images')->useDisk('public_media');
    }
}
