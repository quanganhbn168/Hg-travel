<?php

namespace App\Providers;

use App\Observers\ManagedMediaObserver;
use App\Services\MediaReferenceService;
use App\Support\AssetVersion;
use App\Support\MediaFields;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(MediaReferenceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Canonical, og:url, structured data and sitemap all follow APP_URL, not the request host.
        if ($appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
        }
        Paginator::useBootstrapFive();
        Blade::directive('versioned', fn (string $expression): string => '<?php echo e('.AssetVersion::class.'::url('.$expression.')); ?>');
        foreach (MediaFields::models() as $model) {
            $model::observe(ManagedMediaObserver::class);
        }
    }
}
