<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(): View
    {
        return view('admin.roles.index', ['roles' => $this->roleService->paginate()]);
    }

    public function create(): View
    {
        return view('admin.roles.create', $this->roleService->formContext());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = $this->roleService->create($request->validated());

        return redirect()->route('admin.roles.edit', $role)->with('success', 'Đã tạo vai trò.');
    }

    public function edit(Role $role): View
    {
        abort_unless($role->guard_name === 'web', 404);

        return view('admin.roles.edit', $this->roleService->formContext($role));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        $this->roleService->update($role, $request->validated());

        return back()->with('success', 'Đã cập nhật vai trò.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        $this->roleService->delete($role);

        return back()->with('success', 'Đã xóa vai trò.');
    }
}
