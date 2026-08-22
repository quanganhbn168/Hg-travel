<?php

namespace App\View\Composers;

use App\Services\SiteSettingsService;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminViewComposer
{
    public function __construct(
        private readonly SiteSettingsService $siteSettings,
    ) {}

    public function compose(View $view): void
    {
        $favicon = $this->siteSettings->media()->favicon_url;

        $view->with('adminFavicon', $this->assetUrl($favicon) ?: asset('images/logo-hg.png'));
    }

    private function assetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
    }
}
