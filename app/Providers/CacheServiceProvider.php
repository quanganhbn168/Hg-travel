<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Observers\MenuCacheObserver;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Menu::observe(MenuCacheObserver::class);
        MenuItem::observe(MenuCacheObserver::class);
    }
}
