<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class MenuSourceCatalog
{
    /** @return array<string, string> */
    public static function routeOptions(): array
    {
        return [
            'home' => 'Trang chủ',
            'about' => 'Giới thiệu',
            'tours.index' => 'Tất cả tour',
            'services.index' => 'Tất cả dịch vụ',
            'posts.index' => 'Cẩm nang du lịch',
            'booking.create' => 'Đặt tour',
            'contact' => 'Liên hệ',
        ];
    }

    /** @return array<int, array{key: string, label: string, icon: string, items: array<int, array<string, mixed>}>} */
    public function groups(): array
    {
        $groups = [];
        $routeItems = collect(self::routeOptions())
            ->filter(fn (string $label, string $route): bool => Route::has($route))
            ->map(fn (string $label, string $route): array => $this->item(
                key: "route:{$route}",
                label: $label,
                meta: 'Trang hệ thống',
                sourceType: 'native_route',
                sourceId: null,
                routeName: $route,
            ))
            ->values()
            ->all();

        if ($routeItems !== []) {
            $groups[] = ['key' => 'routes', 'label' => 'Trang hệ thống', 'icon' => 'bi-window', 'items' => $routeItems];
        }

        $groups[] = $this->modelGroup('tour-categories', 'Loại hình tour', 'bi-tags', TourCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'tour_category', 'tours.category', 'category');

        $groups[] = $this->modelGroup('tours', 'Tour du lịch', 'bi-compass', Tour::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'tour', 'tours.show', 'tour');

        $groups[] = $this->modelGroup('destinations', 'Điểm đến', 'bi-geo-alt', Destination::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'destination', 'tours.index', 'destination');

        $groups[] = $this->modelGroup('service-categories', 'Danh mục dịch vụ', 'bi-grid', ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'service_category', 'services.category', 'category');

        $groups[] = $this->modelGroup('services', 'Dịch vụ', 'bi-briefcase', Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'service', 'services.show', 'service');

        $groups[] = $this->modelGroup('pages', 'Trang tĩnh', 'bi-file-earmark-text', Page::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']), 'page', 'pages.show', 'page');

        $groups[] = $this->modelGroup('posts', 'Bài viết / cẩm nang', 'bi-journal-text', Post::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->latest('id')
            ->get(['id', 'name', 'slug']), 'post', 'posts.show', 'post');

        return array_values(array_filter($groups, fn (?array $group): bool => $group !== null && $group['items'] !== []));
    }

    /** @param Collection<int, object> $models */
    private function modelGroup(string $key, string $label, string $icon, Collection $models, string $sourceType, string $routeName, string $routeParameter): ?array
    {
        if ($models->isEmpty() || ! Route::has($routeName)) {
            return null;
        }

        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'items' => $models->map(fn (object $model): array => $this->item(
                key: "{$sourceType}:{$model->getKey()}",
                label: (string) $model->name,
                meta: $label,
                sourceType: $sourceType,
                sourceId: (int) $model->getKey(),
                routeName: null,
                url: null,
                routeParameter: $routeParameter,
                slug: (string) $model->slug,
            ))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function item(string $key, string $label, string $meta, string $sourceType, ?int $sourceId, ?string $routeName, ?string $url = null, ?string $routeParameter = null, ?string $slug = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'meta' => $meta,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'route_name' => $routeName,
            'route_parameter' => $routeParameter,
            'slug' => $slug,
            'url' => $url,
            'target' => '_self',
        ];
    }
}
