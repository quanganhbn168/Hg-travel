<?php

namespace App\Providers;

use App\Models\Destination;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Observers\SlugObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Destination::observe(SlugObserver::class);
        TourCategory::observe(SlugObserver::class);
        Tour::observe(SlugObserver::class);
        Page::observe(SlugObserver::class);
        PostCategory::observe(SlugObserver::class);
        Post::observe(SlugObserver::class);
    }
}
