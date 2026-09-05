<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;

class TravelServiceCatalog
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return Service::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => $this->serviceData($service))
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function categories(): array
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ServiceCategory $category): array => [
                'slug' => $category->slug,
                'label' => $category->name,
                'kicker' => $category->kicker,
                'icon' => $category->icon ?: 'bi-grid',
                'description' => $category->description,
                'service_count' => (int) $category->services_count,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function byCategory(string $slug): array
    {
        return Service::query()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('slug', $slug)->where('is_active', true))
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => $this->serviceData($service))
            ->all();
    }

    /** @return array<string, mixed>|null */
    public function find(string $slug): ?array
    {
        $service = Service::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->first();

        return $service ? $this->serviceData($service) : null;
    }

    /** @return array<string, mixed>|null */
    public function category(string $slug): ?array
    {
        $category = ServiceCategory::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->withCount(['services' => fn ($query) => $query->where('is_active', true)])
            ->first();

        return $category ? [
            'slug' => $category->slug,
            'label' => $category->name,
            'kicker' => $category->kicker,
            'icon' => $category->icon ?: 'bi-grid',
            'description' => $category->description,
            'service_count' => (int) $category->services_count,
        ] : null;
    }

    /** @return array<int, string> */
    public function contactSubjects(): array
    {
        return collect($this->all())->pluck('title')->values()->all();
    }

    /** @return array<int, array<string, string>> */
    public function homeCards(): array
    {
        return collect($this->all())->map(fn (array $service): array => [
            'icon' => $service['icon'],
            'title' => $service['title'],
            'description' => $service['description'],
            'url' => route('services.show', ['service' => $service['slug']]),
        ])->all();
    }

    /** @return array<string, mixed> */
    private function serviceData(Service $service): array
    {
        return [
            'id' => $service->getKey(),
            'slug' => $service->slug,
            'category_slug' => $service->category?->slug,
            'category' => $service->category?->name,
            'title' => $service->name,
            'icon' => $service->icon ?: 'bi-check2-circle',
            'description' => $service->description,
            'intro' => $service->intro ?: $service->description,
            'benefits' => $service->benefits ?: [],
            'cover_image' => app(MediaReferenceService::class)->url($service->cover_image),
            'seo_title' => $service->seo_title,
            'seo_description' => $service->seo_description ?: $service->intro,
        ];
    }
}
