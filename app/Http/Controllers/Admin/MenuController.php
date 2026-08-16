<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View { return view('admin.menus.index', ['menus' => Menu::withCount('items')->orderBy('location')->paginate(20)]); }
    public function create(): View { return view('admin.menus.form', ['menu' => new Menu(['is_active' => true]), 'items' => collect()]); }
    public function store(Request $request): RedirectResponse { $menu = Menu::create($this->payload($request, true)); return to_route('admin.menus.edit', $menu)->with('success', 'Đã tạo menu.'); }
    public function edit(Menu $menu): View { return view('admin.menus.form', ['menu' => $menu, 'items' => $menu->items()->with('parent')->get()]); }
    public function update(Request $request, Menu $menu): RedirectResponse { $menu->update($this->payload($request)); return back()->with('success', 'Đã cập nhật menu.'); }
    public function destroy(Menu $menu): RedirectResponse { $menu->delete(); return to_route('admin.menus.index')->with('success', 'Đã xóa menu.'); }
    public function storeItem(Request $request, Menu $menu): RedirectResponse { $menu->items()->create($this->itemPayload($request, $menu)); return back()->with('success', 'Đã thêm mục menu.'); }
    public function updateItem(Request $request, Menu $menu, MenuItem $item): RedirectResponse { abort_unless($item->menu_id === $menu->id, 404); $item->update($this->itemPayload($request, $menu)); return back()->with('success', 'Đã cập nhật mục menu.'); }
    public function destroyItem(Menu $menu, MenuItem $item): RedirectResponse { abort_unless($item->menu_id === $menu->id, 404); $item->delete(); return back()->with('success', 'Đã xóa mục menu.'); }
    private function payload(Request $request, bool $creating = false): array { $data = $request->validate(['name' => ['required','string','max:255'], 'location' => ['required','string','max:50', $creating ? 'unique:menus,location' : 'unique:menus,location,'.$request->route('menu')->id], 'is_active' => ['nullable','boolean']]); return ['name' => trim($data['name']), 'location' => Str::slug($data['location']), 'is_active' => $request->boolean('is_active')]; }
    private function itemPayload(Request $request, Menu $menu): array { $data = $request->validate(['parent_id' => ['nullable','integer','exists:menu_items,id'], 'title' => ['required','string','max:255'], 'url' => ['nullable','string','max:2048'], 'route_name' => ['nullable','string','max:255'], 'target' => ['nullable','in:_self,_blank'], 'position' => ['nullable','integer','min:0'], 'is_active' => ['nullable','boolean']]); if (filled($data['parent_id'])) abort_unless($menu->items()->whereKey($data['parent_id'])->exists(), 422); return $data + ['position' => (int) ($data['position'] ?? 0), 'target' => $data['target'] ?? '_self', 'is_active' => $request->boolean('is_active')]; }
}
