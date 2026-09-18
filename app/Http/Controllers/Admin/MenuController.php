<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexMenuRequest;
use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuItemRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menus) {}

    public function index(IndexMenuRequest $request): View
    {
        return view('admin.menus.index', [
            'menus' => $this->menus->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.form', $this->menus->formContext());
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = $this->menus->create($request->validated());

        return to_route('admin.menus.edit', $menu)
            ->with('success', 'Đã tạo menu và cấu trúc menu.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.form', $this->menus->formContext($menu));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->menus->update($menu, $request->validated());

        return back()->with('success', 'Đã cập nhật menu và cấu trúc menu.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->menus->delete($menu);

        return to_route('admin.menus.index')
            ->with('success', 'Đã xóa menu.');
    }

    public function storeItem(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $this->menus->addItem($menu, $request->validated());

        return back()->with('success', 'Đã thêm mục menu.');
    }

    public function updateItem(
        UpdateMenuItemRequest $request,
        Menu $menu,
        MenuItem $item,
    ): RedirectResponse {
        $this->menus->updateItem($menu, $item, $request->validated());

        return back()->with('success', 'Đã cập nhật mục menu.');
    }

    public function destroyItem(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->menus->deleteItem($menu, $item);

        return back()->with('success', 'Đã xóa mục menu.');
    }
}
