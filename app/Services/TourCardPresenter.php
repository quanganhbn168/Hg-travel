<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Tour;
use Carbon\Carbon;

class TourCardPresenter
{
    public function present(Tour $tour, ?Promotion $promotion = null): array
    {
        $promotion ??= $tour->relationLoaded('promotions') ? $tour->promotions->first() : null;
        $pricing = $tour->priceSummary($promotion);

        $destinations = $tour->relationLoaded('destinations')
            ? $tour->getRelation('destinations')
            : $tour->destinations()->get();
        $primaryDestination = $destinations->first();
        $categories = $tour->relationLoaded('categories')
            ? $tour->getRelation('categories')
            : $tour->categories()->get();

        return [
            'id' => $tour->getKey(),
            'name' => $tour->name,
            'slug' => $tour->slug,
            'booking_open' => (bool) $tour->booking_open,
            'summary' => $tour->summary,
            'image_url' => $this->imageUrl($tour),
            'category' => $categories->pluck('name')->filter()->implode(' · '),
            'category_slug' => $categories->first()?->slug,
            'categories' => $categories->map(fn ($category): array => ['name' => $category->name, 'slug' => $category->slug])->values()->all(),
            'destination' => $destinations->pluck('name')->filter()->implode(' · '),
            'destination_slug' => $primaryDestination?->slug,
            'destinations' => $destinations->map(fn ($destination): array => ['name' => $destination->name, 'slug' => $destination->slug])->values()->all(),
            'duration' => (int) $tour->duration_days.' ngày'.($tour->duration_nights > 0 ? ' '.(int) $tour->duration_nights.' đêm' : ''),
            'duration_days' => (int) $tour->duration_days,
            'transport' => $tour->transport ?: 'Theo chương trình',
            'next_departure' => filled($tour->getAttribute('next_departure_date'))
                ? Carbon::parse($tour->getAttribute('next_departure_date'))->format('d/m/Y')
                : null,
            'price' => $pricing['price'],
            'price_label' => $this->moneyLabel($pricing['price']),
            'sale_price_label' => $pricing['sale_price'] !== null ? $this->moneyLabel($pricing['sale_price']) : null,
            'discount_percent' => $pricing['discount_percent'],
            'schedule_options' => $tour->scheduleOptions(),
            'currency' => $tour->currency ?: 'VND',
        ];
    }

    private function moneyLabel(float $amount): string
    {
        return $amount > 0 ? number_format($amount, 0, ',', '.').'đ' : 'Liên hệ';
    }

    private function imageUrl(Tour $tour): ?string
    {
        if (method_exists($tour, 'getFirstMediaUrl') && ($mediaUrl = $tour->getFirstMediaUrl('tour_images'))) {
            return $mediaUrl;
        }

        $destinations = $tour->relationLoaded('destinations')
            ? $tour->getRelation('destinations')
            : $tour->destinations()->get();
        $primaryDestination = $destinations->first();

        foreach ([$tour->images->first()?->path, $primaryDestination?->cover_image] as $path) {
            if (filled($path)) {
                return app(MediaReferenceService::class)->url($path);
            }
        }

        if ($primaryDestination && method_exists($primaryDestination, 'getFirstMediaUrl') && ($mediaUrl = $primaryDestination->getFirstMediaUrl('cover'))) {
            return $mediaUrl;
        }

        return null;
    }
}
