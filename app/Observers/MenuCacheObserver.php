<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\FrontendMenuService;

class MenuCacheObserver
{
    public function saved(Menu|MenuItem $model): void
    {
        $this->clear();
    }

    public function deleted(Menu|MenuItem $model): void
    {
        $this->clear();
    }

    private function clear(): void
    {
        app(FrontendMenuService::class)->clear();
    }
}
