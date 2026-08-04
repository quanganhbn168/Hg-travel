<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Slug;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\View\View;

class SlugController extends Controller
{
    public function showByDomain(string $domain, string $slug): View
    {
        $slugModel = Slug::query()
            ->with('sluggable')
            ->where('slug', $slug)
            ->where('locale', app()->getLocale())
            ->firstOrFail();

        $sluggable = $slugModel->sluggable;

        abort_unless($sluggable, 404);

        if ($sluggable instanceof Tour) {
            abort_unless(in_array($domain, ['tour', 'tours'], true), 404);

            return app(TourController::class)->show($sluggable);
        }

        if ($sluggable instanceof TourCategory) {
            abort_unless($domain === 'danh-muc-tour', 404);

            return app(TourController::class)->category(request(), $sluggable);
        }

        abort(404);
    }
}
