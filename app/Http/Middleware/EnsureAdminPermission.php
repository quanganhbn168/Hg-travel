<?php

namespace App\Http\Middleware;

use App\Support\AdminIndexRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin');
        $permission = $this->permissionFor($request);

        if (! $user || ! $user->hasPermissionTo($permission, 'web')) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }

    private function permissionFor(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        return match (true) {
            $routeName === 'admin.dashboard' => 'dashboard.view',
            $routeName === 'admin.about.edit' => 'about.view',
            $routeName === 'admin.about.update' => 'about.update',
            $routeName === 'admin.settings.website',
            $routeName === 'admin.settings.index',
            $routeName === 'admin.settings.business',
            $routeName === 'admin.settings.media',
            $routeName === 'admin.settings.seo',
            $routeName === 'admin.settings.contact',
            $routeName === 'admin.settings.tour' => 'settings.view',
            str_ends_with($routeName, '.settings.website.update'),
            str_ends_with($routeName, '.settings.business.update'),
            str_ends_with($routeName, '.settings.media.update'),
            str_ends_with($routeName, '.settings.seo.update'),
            str_ends_with($routeName, '.settings.contact.update'),
            str_ends_with($routeName, '.settings.tour.update') => 'settings.update',
            $routeName === 'admin.media.index',
            $routeName === 'admin.media.list' => 'media.view',
            str_starts_with($routeName, 'admin.media.upload.') => 'media.upload',
            $routeName === 'admin.contact-submissions.index',
            $routeName === 'admin.contact-submissions.edit' => 'contact-submissions.view',
            $routeName === 'admin.contact-submissions.update' => 'contact-submissions.update',
            $routeName === 'admin.common.bulk-action' => $this->bulkPermission($request),
            $routeName === 'admin.common.reorder' => $this->resourcePermission((string) $request->input('resource'), 'update'),
            $routeName === 'admin.common.toggle' => $this->resourcePermission((string) $request->input('resource'), 'update'),
            str_starts_with($routeName, 'admin.tours.import.') => 'tours.update',
            default => $this->resourceRoutePermission($routeName),
        };
    }

    private function resourceRoutePermission(string $routeName): string
    {
        foreach ([
            'destinations', 'travel-moments', 'tour-categories', 'service-categories', 'services',
            'tours', 'bookings', 'pages', 'post-categories', 'posts', 'sliders', 'testimonials',
            'promotions', 'coupons', 'menus', 'users', 'roles',
        ] as $resource) {
            $prefix = 'admin.'.$resource.'.';

            if (str_starts_with($routeName, $prefix)) {
                return $this->resourcePermission($resource, str($routeName)->afterLast('.')->toString());
            }
        }

        return 'admin.access';
    }

    private function resourcePermission(string $resource, string $action): string
    {
        $registryResource = str_replace('-', '_', $resource);
        $registryPermissionResource = AdminIndexRegistry::permissionResourceFor($registryResource);

        if ($registryPermissionResource !== '') {
            $resource = str_replace('_', '-', $registryPermissionResource);
        }

        $ability = match ($action) {
            'index', 'show' => 'view',
            'edit' => 'update',
            'create', 'store' => 'create',
            'update', 'activate', 'deactivate', 'confirm', 'cancel', 'complete', 'reorder' => 'update',
            'destroy', 'delete' => 'delete',
            default => 'view',
        };

        return $resource.'.'.$ability;
    }

    private function bulkPermission(Request $request): string
    {
        $resource = (string) $request->input('resource');
        $action = (string) $request->input('action');

        return $this->resourcePermission($resource, $action === 'delete' ? 'delete' : 'update');
    }
}
