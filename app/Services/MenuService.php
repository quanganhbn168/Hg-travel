<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuService
{
    public function __construct(private readonly MenuSourceCatalog $sourceCatalog) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Menu::query()
            ->withCount('items')
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                )
            )
            ->when(
                ($filters['status'] ?? null) === 'active',
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                ($filters['status'] ?? null) === 'inactive',
                fn ($query) => $query->where('is_active', false)
            )
            ->orderBy('location')
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function formContext(?Menu $menu = null): array
    {
        $menu ??= new Menu(['is_active' => true]);

        return [
            'menu' => $menu,
            'menuItems' => $menu->exists
                ? $this->menuTree($menu->items()->get())
                : [],
            'sourceGroups' => $this->sourceCatalog->groups(),
        ];
    }

    public function create(array $data): Menu
    {
        return DB::transaction(function () use ($data): Menu {
            $menu = Menu::create($this->menuPayload($data));
            $this->syncBuilderItems(
                $menu,
                $this->builderItems($data['items_json'] ?? '[]'),
            );

            return $menu;
        });
    }

    public function update(Menu $menu, array $data): void
    {
        DB::transaction(function () use ($menu, $data): void {
            $menu->update($this->menuPayload($data));

            if ((bool) ($data['items_present'] ?? false)) {
                $this->syncBuilderItems(
                    $menu,
                    $this->builderItems($data['items_json'] ?? '[]'),
                );
            }
        });
    }

    public function delete(Menu $menu): void
    {
        $menu->delete();
    }

    public function addItem(Menu $menu, array $data): MenuItem
    {
        return $menu->items()->create($this->itemPayload($data, $menu));
    }

    public function updateItem(Menu $menu, MenuItem $item, array $data): void
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->update($this->itemPayload($data, $menu, $item));
    }

    public function deleteItem(Menu $menu, MenuItem $item): void
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->delete();
    }

    public function ensureItemBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_unless((int) $item->menu_id === (int) $menu->id, 404);
    }

    private function menuPayload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'location' => $data['location'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function builderItems(string $json): array
    {
        $items = json_decode($json, true);

        if (! is_array($items)) {
            throw ValidationException::withMessages([
                'items_json' => 'Cấu trúc menu không hợp lệ.',
            ]);
        }

        return array_values($items);
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncBuilderItems(Menu $menu, array $items): void
    {
        $keptIds = [];
        $this->syncItemLevel($menu, $items, null, $keptIds);

        $menu->items()
            ->whereNotIn('id', $keptIds ?: [0])
            ->delete();
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncItemLevel(
        Menu $menu,
        array $items,
        ?int $parentId,
        array &$keptIds,
    ): void {
        foreach (array_values($items) as $position => $data) {
            if (! is_array($data)) {
                throw ValidationException::withMessages([
                    'items_json' => 'Một mục menu có cấu trúc không hợp lệ.',
                ]);
            }

            $itemId = filled($data['id'] ?? null)
                ? (int) $data['id']
                : null;

            $item = $itemId
                ? $menu->items()->whereKey($itemId)->first()
                : null;

            if ($itemId && ! $item) {
                throw ValidationException::withMessages([
                    'items_json' => 'Một mục menu không thuộc menu đang sửa.',
                ]);
            }

            $payload = $this->builderItemPayload(
                $data,
                $parentId,
                $position + 1,
            );

            if ($item) {
                $item->update($payload);
            } else {
                $item = $menu->items()->create($payload);
            }

            $keptIds[] = $item->getKey();

            $children = is_array($data['children'] ?? null)
                ? $data['children']
                : [];

            $this->syncItemLevel(
                $menu,
                $children,
                $item->getKey(),
                $keptIds,
            );
        }
    }

    /** @param array<string, mixed> $data */
    private function builderItemPayload(
        array $data,
        ?int $parentId,
        int $position,
    ): array {
        $title = trim((string) ($data['title'] ?? ''));
        $target = (string) ($data['target'] ?? '_self');
        $linkedSourceType = filled($data['linked_source_type'] ?? null)
            ? trim((string) $data['linked_source_type'])
            : null;

        if ($title === '' || mb_strlen($title) > 255) {
            throw ValidationException::withMessages([
                'items_json' => 'Mỗi mục menu phải có nhãn hiển thị hợp lệ.',
            ]);
        }

        if (! in_array($target, ['_self', '_blank'], true)) {
            throw ValidationException::withMessages([
                'items_json' => 'Cách mở liên kết không hợp lệ.',
            ]);
        }

        if ($linkedSourceType !== null && ! in_array($linkedSourceType, [
            'native_route',
            'tour_category',
            'tour',
            'destination',
            'service_category',
            'service',
            'page',
            'post',
            'custom',
        ], true)) {
            throw ValidationException::withMessages([
                'items_json' => 'Nguồn liên kết menu không hợp lệ.',
            ]);
        }

        $url = trim((string) ($data['url'] ?? ''));
        $routeName = trim((string) ($data['route_name'] ?? ''));
        $linkedSourceId = filled($data['linked_source_id'] ?? null)
            ? (int) $data['linked_source_id']
            : null;

        if ($linkedSourceType === 'custom' && ($url === '' || $url === '#')) {
            throw ValidationException::withMessages([
                'items_json' => "Liên kết custom của mục '{$title}' không được để trống.",
            ]);
        }

        return [
            'parent_id' => $parentId,
            'title' => $title,
            'url' => $url !== '' ? $url : null,
            'route_name' => $routeName !== '' ? $routeName : null,
            'target' => $target,
            'position' => $position,
            'linked_source_id' => $linkedSourceId,
            'linked_source_type' => $linkedSourceType,
            'is_active' => filter_var(
                $data['is_active'] ?? true,
                FILTER_VALIDATE_BOOLEAN,
            ),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function menuTree(Collection $items): array
    {
        $byParent = $items->groupBy(
            fn (MenuItem $item): string => (string) ($item->parent_id ?? 0)
        );

        $visited = [];

        $build = function (int $parentId) use (&$build, $byParent, &$visited): array {
            return $byParent->get((string) $parentId, collect())
                ->flatMap(function (MenuItem $item) use (&$build, &$visited): array {
                    if (isset($visited[$item->getKey()])) {
                        return [];
                    }

                    $visited[$item->getKey()] = true;

                    $sourceType = $item->linked_source_type
                        ?: ($item->route_name ? 'native_route' : 'custom');

                    $row = [
                        'key' => 'item-'.$item->getKey(),
                        'id' => $item->getKey(),
                        'title' => $item->title,
                        'url' => $item->url,
                        'route_name' => $item->route_name,
                        'target' => $item->target ?: '_self',
                        'is_active' => (bool) $item->is_active,
                        'linked_source_id' => $item->linked_source_id,
                        'linked_source_type' => $sourceType,
                        'link_type_label' => $this->linkTypeLabel($sourceType),
                        'link_summary' => $item->route_name ?: ($item->url ?: 'Chưa có liên kết'),
                        'parent_key' => $item->parent_id
                            ? 'item-'.$item->parent_id
                            : '',
                    ];

                    return array_merge(
                        [$row],
                        $build((int) $item->getKey()),
                    );
                })
                ->values()
                ->all();
        };

        return $build(0);
    }

    private function linkTypeLabel(string $sourceType): string
    {
        return [
            'native_route' => 'Trang hệ thống',
            'tour_category' => 'Loại hình tour',
            'tour' => 'Tour du lịch',
            'destination' => 'Điểm đến',
            'service_category' => 'Danh mục dịch vụ',
            'service' => 'Dịch vụ',
            'page' => 'Trang tĩnh',
            'post' => 'Bài viết',
            'custom' => 'Liên kết custom',
        ][$sourceType] ?? 'Liên kết';
    }

    private function itemPayload(
        array $data,
        Menu $menu,
        ?MenuItem $item = null,
    ): array {
        $parentId = filled($data['parent_id'] ?? null)
            ? (int) $data['parent_id']
            : null;

        if ($parentId) {
            $parent = $menu->items()->whereKey($parentId)->first();

            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Mục menu cha không thuộc menu hiện tại.',
                ]);
            }

            if ($item && $parentId === (int) $item->getKey()) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Mục menu không thể là cha của chính nó.',
                ]);
            }
        }

        return [
            'parent_id' => $parentId,
            'title' => trim($data['title']),
            'url' => filled($data['url'] ?? null)
                ? trim($data['url'])
                : null,
            'route_name' => filled($data['route_name'] ?? null)
                ? trim($data['route_name'])
                : null,
            'target' => $data['target'] ?? '_self',
            'position' => (int) ($data['position'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
