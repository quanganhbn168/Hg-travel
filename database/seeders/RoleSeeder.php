<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $allPermissions = Permission::query()->where('guard_name', 'web')->get();
        $adminRole->syncPermissions($allPermissions);

        foreach ([
            'tour-manager' => ['admin.access', 'dashboard.view', 'destinations.*', 'tour-categories.*', 'tours.*', 'bookings.view'],
            'booking-manager' => ['admin.access', 'dashboard.view', 'tours.view', 'bookings.*'],
            'content-editor' => ['admin.access', 'dashboard.view', 'pages.*', 'post-categories.*', 'posts.*', 'sliders.*', 'testimonials.*', 'travel-moments.*'],
            'marketing-manager' => ['admin.access', 'dashboard.view', 'tours.view', 'promotions.*', 'coupons.*', 'menus.*'],
            'settings-manager' => ['admin.access', 'dashboard.view', 'settings.*', 'media.*'],
        ] as $roleName => $patterns) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($allPermissions->filter(function (Permission $permission) use ($patterns): bool {
                foreach ($patterns as $pattern) {
                    if (str_ends_with($pattern, '*') && str_starts_with($permission->name, rtrim($pattern, '*'))) {
                        return true;
                    }

                    if ($permission->name === $pattern) {
                        return true;
                    }
                }

                return false;
            }));
        }
    }
}
