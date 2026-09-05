<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\AboutPageService;
use App\Services\SiteSettingsService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(AboutPageService $about, SiteSettingsService $siteSettings): View
    {
        $page = $about->current();
        $profileContent = $about->profileContent($page);
        $supportServiceIds = collect(data_get($profileContent, 'support.service_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        return view('frontend.about', [
            ...$about->presentationData($page, $siteSettings->general()),
            'about' => $page,
            'profileContent' => $profileContent,
            'siteSettings' => $siteSettings->general(),
            'impactStats' => $siteSettings->impactStats(),
            'supportServices' => Service::query()
                ->where('is_active', true)
                ->whereIn('id', $supportServiceIds)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
