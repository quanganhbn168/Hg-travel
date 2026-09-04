<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuSourceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly MenuSourceCatalog $sourceCatalog) {}

    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::withCount('items')->orderBy('location')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.form', [
            'menu' => new Menu(['is_active' => true]),
            'menuItems' => [],
            'sourceGroups' => $this->sourceCatalog->groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $menu = DB::transaction(function () use ($request): Menu {
            $menu = Menu::create($this->menuPayload($request));
            $this->syncBuilderItems($menu, $this->builderItems($request));

            return $menu;
        });

        return to_route('admin.menus.edit', $menu)->with('success', 'Đã tạo menu và cấu trúc menu.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.form', [
            'menu' => $menu,
            'menuItems' => $this->menuTree($menu->items()->get()),
            'sourceGroups' => $this->sourceCatalog->groups(),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        DB::transaction(function () use ($request, $menu): void {
            $menu->update($this->menuPayload($request, $menu));

            if ($request->boolean('items_present')) {
                $this->syncBuilderItems($menu, $this->builderItems($request));
            }
        });

        return back()->with('success', 'Đã cập nhật menu và cấu trúc menu.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return to_route('admin.menus.index')->with('success', 'Đã xóa menu.');
    }

    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $menu->items()->create($this->itemPayload($request, $menu));

        return back()->with('success', 'Đã thêm mục menu.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $item->update($this->itemPayload($request, $menu));

        return back()->with('success', 'Đã cập nhật mục menu.');
    }

    public function destroyItem(Menu $menu, MenuItem $item): RedirectResponse
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $item->delete();

        return back()->with('success', 'Đã xóa mục menu.');
    }

    private function menuPayload(Request $request, ?Menu $menu = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => [
                'required',
                Rule::in(['header', 'footer']),
                Rule::unique('menus', 'location')->ignore($menu?->getKey()),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => trim($data['name']),
            'location' => Str::slug($data['location']),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function builderItems(Request $request): array
    {
        $items = json_decode((string) $request->input('items_json', '[]'), true);

        if (! is_array($items)) {
            throw ValidationException::withMessages(['items_json' => 'Cấu trúc menu không hợp lệ.']);
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
    private function syncItemLevel(Menu $menu, array $items, ?int $parentId, array &$keptIds): void
    {
        foreach (array_values($items) as $position => $data) {
            if (! is_array($data)) {
                continue;
            }

            $item = null;
            $itemId = filled($data['id'] ?? null) ? (int) $data['id'] : null;

            if ($itemId) {
                $item = $menu->items()->whereKey($itemId)->first();

                if (! $item) {
                    throw ValidationException::withMessages(['items_json' => 'Một mục menu không thuộc menu đang sửa.']);
                }
            }

            $payload = $this->builderItemPayload($data, $parentId, $position + 1);

            if ($item) {
                $item->update($payload);
            } else {
                $item = $menu->items()->create($payload);
            }

            $keptIds[] = $item->getKey();
            $children = is_array($data['children'] ?? null) ? $data['children'] : [];
            $this->syncItemLevel($menu, $children, $item->getKey(), $keptIds);
        }
    }

    /** @param array<string, mixed> $data */
    private function builderItemPayload(array $data, ?int $parentId, int $position): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $target = (string) ($data['target'] ?? '_self');
        $linkedSourceType = filled($data['linked_source_type'] ?? null)
            ? trim((string) $data['linked_source_type'])
            : null;

        if ($title === '' || mb_strlen($title) > 255) {
            throw ValidationException::withMessages(['items_json' => 'Mỗi mục menu phải có nhãn hiển thị hợp lệ.']);
        }

        if (! in_array($target, ['_self', '_blank'], true)) {
            throw ValidationException::withMessages(['items_json' => 'Cách mở liên kết không hợp lệ.']);
        }

        if ($linkedSourceType !== null && ! in_array($linkedSourceType, [
            'native_route', 'tour_category', 'tour', 'destination', 'service_category', 'service', 'page', 'post', 'custom',
        ], true)) {
            throw ValidationException::withMessages(['items_json' => 'Nguồn liên kết menu không hợp lệ.']);
        }

        $url = trim((string) ($data['url'] ?? ''));
        $routeName = trim((string) ($data['route_name'] ?? ''));
        $linkedSourceId = filled($data['linked_source_id'] ?? null) ? (int) $data['linked_source_id'] : null;

        if ($linkedSourceType === 'custom' && ($url === '' || $url === '#')) {
            throw ValidationException::withMessages(["items_json" => "Liên kết custom của mục '{$title}' không được để trống."]);
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
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function menuTree(Collection $items): array
    {
        $byParent = $items->groupBy(fn (MenuItem $item): string => (string) ($item->parent_id ?? 0));

        $build = function (int $parentId) use (&$build, $byParent): array {
            return $byParent->get((string) $parentId, collect())
                ->flatMap(function (MenuItem $item) use (&$build): array {
                    $sourceType = $item->linked_source_type ?: ($item->route_name ? 'native_route' : 'custom');
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
                        'parent_key' => $item->parent_id ? 'item-'.$item->parent_id : '',
                    ];

                    return array_merge([$row], $build((int) $item->getKey()));
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

    /** @return array<string, mixed> */
    private function itemPayload(Request $request, Menu $menu): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'in:_self,_blank'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (filled($data['parent_id'])) {
            abort_unless($menu->items()->whereKey($data['parent_id'])->exists(), 422);
        }

        return $data + [
            'position' => (int) ($data['position'] ?? 0),
            'target' => $data['target'] ?? '_self',
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
