<?php

namespace App\Providers;

use App\View\Composers\MasterViewComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer(['layouts.master', 'frontend.*'], MasterViewComposer::class);
    }
}
