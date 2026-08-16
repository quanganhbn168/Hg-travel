<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\AboutProfileCatalog;
use App\Services\AboutPageService;
use App\Services\SiteSettingsService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(AboutPageService $about, AboutProfileCatalog $profile, SiteSettingsService $siteSettings): View
    {
        $website = $siteSettings->website();

        return view('frontend.about', [
            'about' => $about->current(),
            'profile' => $profile->profile($siteSettings->general()),
            'impactStats' => [
                ['number' => $website->impact_stat_one_number, 'label' => $website->impact_stat_one_label],
                ['number' => $website->impact_stat_two_number, 'label' => $website->impact_stat_two_label],
                ['number' => $website->impact_stat_three_number, 'label' => $website->impact_stat_three_label],
                ['number' => $website->impact_stat_four_number, 'label' => $website->impact_stat_four_label],
            ],
        ]);
    }
}
