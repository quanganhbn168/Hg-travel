<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\TourCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class FrontendMenuService
{
    public function items(string $location): array
    {
        $key = $this->cacheKey($location);
        $ttl = (int) config('frontend.cache.menus.ttl', 3600);

        if ($location === 'header' || ! config('frontend.cache.menus.enabled', true)) {
            return $this->load($location);
        }

        return Cache::remember($key, $ttl, fn (): array => $this->load($location));
    }

    public function clear(?string $location = null): void
    {
        foreach (($location ? [$location] : ['header', 'footer']) as $menuLocation) {
            Cache::forget($this->cacheKey($menuLocation));
        }
    }

    private function load(string $location): array
    {
        $menu = Menu::query()
            ->where('location', $location)
            ->where('is_active', true)
            ->with(['items' => function ($query): void {
                $query->whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => function ($children): void {
                        $children->where('is_active', true)->with(['children' => fn ($grandchildren) => $grandchildren->where('is_active', true)]);
                    }]);
            }])
            ->first();

        if (! $menu) {
            return $this->fallback($location);
        }

        $items = $menu->items
            ->map(fn (MenuItem $item): array => $this->mapItem($item))
            ->values()
            ->all();

        return $location === 'header'
            ? $this->attachServiceMenus($this->attachTourMenus($items))
            : array_values(array_filter($items, fn (array $item): bool => $item['title'] !== 'Điểm đến nổi bật'));
    }

    private function mapItem(MenuItem $item): array
    {
        return [
            'title' => $item->title,
            'url' => $this->resolveUrl($item->route_name, $item->url),
            'route_name' => $item->route_name,
            'target' => $item->target ?: '_self',
            'children' => $item->children->map(fn (MenuItem $child): array => $this->mapItem($child))->values()->all(),
        ];
    }

    private function resolveUrl(?string $routeName, ?string $url): string
    {
        if ($routeName && Route::has($routeName)) {
            return route($routeName);
        }

        if (blank($url)) {
            return '#';
        }

        return Str::startsWith($url, ['http://', 'https://', '#', '/', 'mailto:', 'tel:']) ? $url : url($url);
    }

    private function fallback(string $location): array
    {
        $items = $location === 'footer'
            ? [
                ['title' => 'Tour du lịch', 'url' => route('tours.index')],
                ['title' => 'Cẩm nang du lịch', 'url' => route('posts.index')],
                ['title' => 'Về chúng tôi', 'url' => route('about')],
            ]
            : [
                ['title' => 'Trang chủ', 'url' => route('home')],
                ['title' => 'Tour nước ngoài', 'url' => route('tours.index', ['scope' => 'international'])],
                ['title' => 'Tour trong nước', 'url' => route('tours.index', ['scope' => 'domestic'])],
                ['title' => 'Dịch vụ', 'url' => route('services.index')],
                ['title' => 'Blog & cẩm nang', 'url' => route('posts.index')],
            ];

        $items = array_map(fn (array $item): array => $item + ['route_name' => null, 'target' => '_self', 'children' => []], $items);

        return $location === 'header' ? $this->attachServiceMenus($this->attachTourMenus($items)) : $items;
    }

    /**
     * Keep the managed top-level menu while replacing stale service anchors
     * with the current category/detail URLs from the shared service catalog.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function attachServiceMenus(array $items): array
    {
        $catalog = app(TravelServiceCatalog::class);

        return array_map(function (array $item) use ($catalog): array {
            if ($item['title'] !== 'Dịch vụ') {
                return $item;
            }

            $item['url'] = route('services.index');
            $item['children'] = array_map(function (array $category) use ($catalog): array {
                return [
                    'title' => $category['label'],
                    'url' => route('services.category', ['category' => $category['slug']]),
                    'route_name' => null,
                    'target' => '_self',
                    'children' => array_map(fn (array $service): array => [
                        'title' => $service['title'],
                        'url' => route('services.show', ['service' => $service['slug']]),
                        'route_name' => null,
                        'target' => '_self',
                        'children' => [],
                    ], $catalog->byCategory($category['slug'])),
                ];
            }, $catalog->categories());

            return $item;
        }, $items);
    }

    /**
     * Build a three-level tour menu from the curated category roots and the
     * actual destinations that currently have published tours.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function attachTourMenus(array $items): array
    {
        $definitions = [
            'Tour nước ngoài' => [
                'scope' => 'international',
                'categories' => ['tour-chau-a', 'tour-chau-au', 'tour-chau-uc', 'tour-chau-my', 'tour-chau-phi'],
            ],
            'Tour trong nước' => [
                'scope' => 'domestic',
                'categories' => ['tour-mien-bac', 'tour-mien-trung', 'tour-mien-nam', 'tour-mien-tay'],
            ],
        ];

        $categorySlugs = collect($definitions)->pluck('categories')->flatten()->all();
        $categories = TourCategory::query()
            ->whereIn('slug', $categorySlugs)
            ->where('is_active', true)
            ->get(['id', 'name', 'slug'])
            ->keyBy('slug');

        $destinationRoots = Destination::query()
            ->whereIn('slug', collect($categorySlugs)->map(fn (string $slug): string => Str::after($slug, 'tour-'))->all())
            ->where('is_active', true)
            ->get(['id', 'slug'])
            ->keyBy('slug');

        $publishedDestinations = Destination::query()
            ->where('is_active', true)
            ->whereHas('tours', function ($query): void {
                $query
                    ->where('is_active', true)
                    ->where('status', 'published')
                    ->where(fn ($published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug'])
            ->groupBy('parent_id');

        return array_map(function (array $item) use ($definitions, $categories, $destinationRoots, $publishedDestinations): array {
            if (! isset($definitions[$item['title']])) {
                return $item;
            }

            $definition = $definitions[$item['title']];
            $item['url'] = route('tours.index', ['scope' => $definition['scope']]);
            $item['children'] = collect($definition['categories'])->map(function (string $categorySlug) use ($categories, $destinationRoots, $publishedDestinations): array {
                $category = $categories->get($categorySlug);
                $destinationRoot = $destinationRoots->get(Str::after($categorySlug, 'tour-'));

                return [
                    'title' => $category?->name ?: Str::headline(Str::after($categorySlug, 'tour-')),
                    'url' => $category ? route('tours.index', ['category' => $category->slug]) : route('tours.index'),
                    'route_name' => null,
                    'target' => '_self',
                    'children' => collect($destinationRoot ? $publishedDestinations->get($destinationRoot->id, collect()) : [])
                        ->map(fn (Destination $destination): array => [
                            'title' => $destination->name,
                            'url' => route('tours.index', ['destination' => $destination->slug]),
                            'route_name' => null,
                            'target' => '_self',
                            'children' => [],
                        ])
                        ->values()
                        ->all(),
                ];
            })->all();

            return $item;
        }, $items);
    }

    private function cacheKey(string $location): string
    {
        return "frontend.menu.v1.{$location}";
    }
}
