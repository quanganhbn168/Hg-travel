<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FrontendMenuService
{
    public function __construct(private readonly MenuLinkResolver $linkResolver) {}

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
            ->first();

        if (! $menu) {
            return $this->fallback($location);
        }

        $menuItems = MenuItem::query()
            ->where('menu_id', $menu->getKey())
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $itemsByParent = $menuItems->groupBy(fn (MenuItem $item): string => (string) ($item->parent_id ?? 0));
        $buildTree = function (int $parentId) use (&$buildTree, $itemsByParent): array {
            return $itemsByParent->get((string) $parentId, collect())
                ->map(function (MenuItem $item) use (&$buildTree): array {
                    $mapped = $this->mapItem($item);
                    $mapped['children'] = $buildTree((int) $item->getKey());

                    return $mapped;
                })
                ->values()
                ->all();
        };
        $items = $buildTree(0);

        return $location === 'header'
            ? $this->attachServiceMenus($this->attachTourMenus($items))
            : array_values(array_filter($items, fn (array $item): bool => $item['title'] !== 'Điểm đến nổi bật'));
    }

    private function mapItem(MenuItem $item): array
    {
        return [
            'title' => $item->title,
            'url' => $this->linkResolver->resolve($item),
            'route_name' => $item->route_name,
            'target' => $item->target ?: '_self',
            'children' => [],
        ];
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
     * Build the tour menu from the active destination tree managed in CMS.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function attachTourMenus(array $items): array
    {
        $definitions = [
            'Tour nước ngoài' => [
                'scope' => 'international',
                'destinations' => ['chau-a', 'chau-au', 'chau-uc', 'chau-my', 'chau-phi'],
            ],
            'Tour trong nước' => [
                'scope' => 'domestic',
                'destinations' => ['mien-bac', 'mien-trung', 'mien-nam', 'mien-tay'],
            ],
        ];

        $destinations = Destination::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug']);
        $destinationRoots = $destinations
            ->whereIn('slug', collect($definitions)->pluck('destinations')->flatten()->unique()->all())
            ->keyBy('slug');
        $destinationsByParent = $destinations->groupBy(fn (Destination $destination): string => (string) ($destination->parent_id ?? 0));

        $buildDestination = function (Destination $destination) use (&$buildDestination, $destinationsByParent): array {
            return [
                'title' => $destination->name,
                'url' => route('tours.index', ['destination' => $destination->slug]),
                'route_name' => null,
                'target' => '_self',
                'children' => $destinationsByParent->get((string) $destination->getKey(), collect())
                    ->map(fn (Destination $child): array => $buildDestination($child))
                    ->values()
                    ->all(),
            ];
        };

        return array_map(function (array $item) use ($definitions, $destinationRoots, $buildDestination): array {
            if (! isset($definitions[$item['title']])) {
                return $item;
            }

            $definition = $definitions[$item['title']];
            $item['url'] = route('tours.index', ['scope' => $definition['scope']]);
            $item['children'] = collect($definition['destinations'])->map(function (string $destinationSlug) use ($destinationRoots, $buildDestination): array {
                $destinationRoot = $destinationRoots->get($destinationSlug);

                return $destinationRoot
                    ? $buildDestination($destinationRoot)
                    : [
                        'title' => Str::headline($destinationSlug),
                        'url' => route('tours.index'),
                        'route_name' => null,
                        'target' => '_self',
                        'children' => [],
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
