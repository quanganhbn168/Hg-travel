<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'admin.access', 'dashboard.view',
            'destinations.view', 'destinations.create', 'destinations.update', 'destinations.delete',
            'tour-categories.view', 'tour-categories.create', 'tour-categories.update', 'tour-categories.delete',
            'tours.view', 'tours.create', 'tours.update', 'tours.delete',
            'bookings.view', 'bookings.create', 'bookings.update', 'bookings.delete',
            'payments.view', 'payments.update',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete',
            'settings.view', 'settings.update',
            'users.view', 'users.create', 'users.update', 'users.delete',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
