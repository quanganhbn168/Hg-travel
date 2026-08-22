<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    private const GUARD = 'web';

    private const GROUP_LABELS = [
        'admin' => 'Quản trị hệ thống',
        'dashboard' => 'Dashboard',
        'about' => 'Trang giới thiệu',
        'media' => 'Media',
        'settings' => 'Cài đặt',
        'destinations' => 'Điểm đến',
        'tour-categories' => 'Loại hình tour',
        'service-categories' => 'Danh mục dịch vụ',
        'services' => 'Dịch vụ',
        'tours' => 'Tour du lịch',
        'bookings' => 'Booking',
        'payments' => 'Thanh toán',
        'pages' => 'Trang tĩnh',
        'post-categories' => 'Danh mục bài viết',
        'posts' => 'Bài viết',
        'sliders' => 'Slider',
        'testimonials' => 'Cảm nhận',
        'promotions' => 'Ưu đãi',
        'coupons' => 'Mã giảm giá',
        'menus' => 'Menu',
        'contact-submissions' => 'Yêu cầu liên hệ',
        'users' => 'Tài khoản admin',
        'roles' => 'Vai trò',
    ];

    private const ACTION_LABELS = [
        'view' => 'Xem danh sách / chi tiết',
        'create' => 'Tạo mới',
        'update' => 'Cập nhật / xử lý',
        'delete' => 'Xóa',
        'upload' => 'Tải lên',
    ];

    public function paginate(): LengthAwarePaginator
    {
        return Role::query()
            ->where('guard_name', self::GUARD)
            ->withCount(['users', 'permissions'])
            ->orderByRaw("CASE WHEN name = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }

    /** @return array{role: Role, groups: array<string, array{label: string, permissions: Collection}>, selectedPermissions: list<string>} */
    public function formContext(?Role $role = null): array
    {
        $role ??= new Role(['guard_name' => self::GUARD]);
        $permissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->orderBy('name')
            ->get();

        $groups = $permissions
            ->groupBy(fn (Permission $permission): string => Str::before($permission->name, '.'))
            ->map(fn (Collection $items, string $group): array => [
                'label' => self::GROUP_LABELS[$group] ?? Str::headline($group),
                'permissions' => $items->values(),
            ])
            ->sortBy(fn (array $group): string => $group['label'])
            ->all();

        return [
            'role' => $role,
            'groups' => $groups,
            'selectedPermissions' => $role->exists
                ? $role->permissions()->pluck('name')->values()->all()
                : [],
            'actionLabels' => self::ACTION_LABELS,
        ];
    }

    /** @param array{name: string, permissions?: array<int, string>|null} $data */
    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => trim($data['name']),
            'guard_name' => self::GUARD,
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        return $role;
    }

    /** @param array{name: string, permissions?: array<int, string>|null} $data */
    public function update(Role $role, array $data): void
    {
        if ($role->name !== 'admin') {
            $role->update(['name' => trim($data['name'])]);
            $this->syncPermissions($role, $data['permissions'] ?? []);

            return;
        }

        // The built-in admin role must remain a full-access recovery role.
        $this->syncPermissions($role, Permission::query()
            ->where('guard_name', self::GUARD)
            ->pluck('name')
            ->all());
    }

    public function delete(Role $role): void
    {
        if ($role->name === 'admin') {
            abort(422, 'Không thể xóa vai trò admin mặc định.');
        }

        if ($role->users()->exists()) {
            abort(422, 'Hãy chuyển tài khoản sang vai trò khác trước khi xóa.');
        }

        $role->delete();
    }

    /** @param array<int, string> $permissionNames */
    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $permissionNames = array_values(array_unique([...$permissionNames, 'admin.access']));

        $permissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->whereIn('name', $permissionNames)
            ->get();

        $role->syncPermissions($permissions);
    }

    public static function actionLabel(string $permissionName, array $actionLabels = self::ACTION_LABELS): string
    {
        return $actionLabels[Str::after($permissionName, '.')] ?? Str::headline(Str::after($permissionName, '.'));
    }
}
