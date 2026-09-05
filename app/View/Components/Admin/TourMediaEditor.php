<?php

namespace App\View\Components\Admin;

use App\Models\Tour;
use App\Models\TourImage;
use App\Services\MediaReferenceService;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class TourMediaEditor extends Component
{
    public ?TourImage $cover;

    public Collection $gallery;

    public function __construct(public Tour $tour)
    {
        $images = $tour->relationLoaded('images') ? $tour->images : collect();
        $this->cover = $images->firstWhere('is_cover', true);
        $positions = array_flip(array_map('intval', (array) old('image_order', [])));
        $this->gallery = $images->reject(fn ($image) => $this->cover && $image->is($this->cover))
            ->sortBy(fn ($image) => $positions[$image->id] ?? (count($positions) + $image->sort_order))
            ->each(fn ($image) => $image->setAttribute('preview_url', app(MediaReferenceService::class)->url($image->path, 'images/placeholder.svg', thumbnail: true)));
    }

    public function render()
    {
        return view('components.admin.tour-media-editor');
    }
}
