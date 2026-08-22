<?php

namespace Database\Seeders;

use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Database\Seeder;

class TourImageSeeder extends Seeder
{
    public function run(): void
    {
        $regionalImages = [
            'mien-bac' => [
                'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=85',
            ],
            'mien-trung' => [
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85',
            ],
            'mien-nam' => [
                'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=85',
            ],
            'mien-tay' => [
                'https://images.unsplash.com/photo-1473445361085-b9a07f55608b?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1200&q=85',
            ],
            'chau-a' => [
                'https://images.unsplash.com/photo-1528360983277-13d401cdc186?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1200&q=85',
            ],
            'chau-au' => [
                'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1200&q=85',
            ],
            'chau-uc' => [
                'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1523482580672-f109ba8cb9be?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1488998427799-e3362cec87c3?auto=format&fit=crop&w=1200&q=85',
            ],
            'chau-my' => [
                'https://images.unsplash.com/photo-1517935706615-2717063c2225?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1444723121867-7a241cacace9?auto=format&fit=crop&w=1200&q=85',
            ],
            'chau-phi' => [
                'https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?auto=format&fit=crop&w=1200&q=85',
                'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=85',
            ],
        ];

        Tour::query()->with(['destinations:id,parent_id,cover_image,slug', 'destinations.parent:id,slug'])->where('status', 'published')->orderBy('sort_order')->each(function (Tour $tour) use ($regionalImages): void {
            $destinationGroup = $tour->destinations->first()?->parent?->slug;
            $images = array_values(array_unique(array_filter([
                $tour->destinations->first()?->cover_image,
                ...($regionalImages[$destinationGroup] ?? $regionalImages['chau-a']),
            ])));

            foreach (array_slice($images, 0, 4) as $index => $image) {
                TourImage::updateOrCreate(['tour_id' => $tour->id, 'sort_order' => $index + 1], [
                    'path' => $image,
                    'alt_text' => $tour->name.' · ảnh '.($index + 1),
                    'is_cover' => $index === 0,
                ]);
            }
        });
    }
}
