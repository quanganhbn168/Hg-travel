<?php

use App\Http\Middleware\ApplyRobotsDirectives;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureAdminPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskCannotBeAccessed;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ApplyRobotsDirectives::class);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->alias([
            'admin' => EnsureAdminAccess::class,
            'admin.permission' => EnsureAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (DiskCannotBeAccessed $exception, Request $request) {
            if ($request->is('admin/media/*')) {
                return response()->json(['message' => 'Máy chủ chưa ghi được thư mục media. Vui lòng kiểm tra cấu hình và quyền ghi thư mục.'], 503);
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'admin/media/*') || $request->expectsJson(),
        );
    })->create();
