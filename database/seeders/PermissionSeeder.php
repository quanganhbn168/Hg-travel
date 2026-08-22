<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'admin.access', 'dashboard.view', 'about.view', 'about.update',
            'media.view', 'media.upload', 'settings.view', 'settings.update',
            'destinations.view', 'destinations.create', 'destinations.update', 'destinations.delete',
            'travel-moments.view', 'travel-moments.create', 'travel-moments.update', 'travel-moments.delete',
            'tour-categories.view', 'tour-categories.create', 'tour-categories.update', 'tour-categories.delete',
            'service-categories.view', 'service-categories.create', 'service-categories.update', 'service-categories.delete',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'tours.view', 'tours.create', 'tours.update', 'tours.delete',
            'bookings.view', 'bookings.create', 'bookings.update', 'bookings.delete',
            'payments.view', 'payments.update',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'post-categories.view', 'post-categories.create', 'post-categories.update', 'post-categories.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete',
            'sliders.view', 'sliders.create', 'sliders.update', 'sliders.delete',
            'testimonials.view', 'testimonials.create', 'testimonials.update', 'testimonials.delete',
            'promotions.view', 'promotions.create', 'promotions.update', 'promotions.delete',
            'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',
            'menus.view', 'menus.create', 'menus.update', 'menus.delete',
            'contact-submissions.view', 'contact-submissions.update',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
