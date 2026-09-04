<?php

namespace App\View\Composers;

use App\Services\SiteSettingsService;
use Illuminate\View\View;

class AdminViewComposer
{
    public function __construct(private readonly SiteSettingsService $siteSettings) {}

    public function compose(View $view): void
    {
        $settings = $this->siteSettings->general();

        $view->with('adminBrandName', $settings->site_name ?: config('app.name', 'Du lịch'));
    }
}
