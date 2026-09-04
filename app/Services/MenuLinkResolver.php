<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MenuLinkResolver
{
    public function resolve(MenuItem $item): string
    {
        $sourceUrl = match ($item->linked_source_type) {
            'tour' => $this->tourUrl($item->linked_source_id),
            'tour_category' => $this->tourCategoryUrl($item->linked_source_id),
            'destination' => $this->destinationUrl($item->linked_source_id),
            'service' => $this->serviceUrl($item->linked_source_id),
            'service_category' => $this->serviceCategoryUrl($item->linked_source_id),
            'page' => $this->pageUrl($item->linked_source_id),
            'post' => $this->postUrl($item->linked_source_id),
            default => null,
        };

        if ($sourceUrl !== null) {
            return $sourceUrl;
        }

        if (filled($item->route_name) && Route::has($item->route_name)) {
            return route($item->route_name);
        }

        if (blank($item->url)) {
            return '#';
        }

        return Str::startsWith($item->url, ['http://', 'https://', '#', '/', 'mailto:', 'tel:'])
            ? $item->url
            : url($item->url);
    }

    private function tourUrl(?int $id): ?string
    {
        $tour = Tour::query()->whereKey($id)->where('is_active', true)->where('status', 'published')->first();

        return $tour?->slug ? route('tours.show', ['tour' => $tour->slug]) : null;
    }

    private function tourCategoryUrl(?int $id): ?string
    {
        $category = TourCategory::query()->whereKey($id)->where('is_active', true)->first();

        return $category?->slug ? route('tours.category', ['category' => $category->slug]) : null;
    }

    private function destinationUrl(?int $id): ?string
    {
        $destination = Destination::query()->whereKey($id)->where('is_active', true)->first();

        return $destination?->slug ? route('tours.index', ['destination' => $destination->slug]) : null;
    }

    private function serviceUrl(?int $id): ?string
    {
        $service = Service::query()->whereKey($id)->where('is_active', true)->first();

        return $service?->slug ? route('services.show', ['service' => $service->slug]) : null;
    }

    private function serviceCategoryUrl(?int $id): ?string
    {
        $category = ServiceCategory::query()->whereKey($id)->where('is_active', true)->first();

        return $category?->slug ? route('services.category', ['category' => $category->slug]) : null;
    }

    private function pageUrl(?int $id): ?string
    {
        $page = Page::query()->whereKey($id)->where('is_active', true)->first();

        return $page?->slug ? route('pages.show', ['page' => $page->slug]) : null;
    }

    private function postUrl(?int $id): ?string
    {
        $post = Post::query()
            ->whereKey($id)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        return $post?->slug ? route('posts.show', ['post' => $post->slug]) : null;
    }
}
