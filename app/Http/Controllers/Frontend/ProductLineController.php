<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProductLine;
use App\Services\StructuredDataService;
use App\Services\TourCardPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\View\View;

class ProductLineController extends Controller
{
    public function __construct(
        private readonly StructuredDataService $structuredData,
        private readonly TourCardPresenter $tourCardPresenter,
    ) {}

    public function show(ProductLine $productLine): View
    {
        abort_unless($productLine->is_active, 404);

        $productLine->load([
            'services' => fn ($query) => $query
                ->where('is_active', true)
                ->with('category')
                ->orderByPivot('sort_order')
                ->orderBy('services.sort_order'),
            'tours' => fn ($query) => $this->publishedTours($query)
                ->with([
                    'category',
                    'destination',
                    'images' => fn ($imageQuery) => $imageQuery->orderByDesc('is_cover')->orderBy('sort_order'),
                    'promotions' => fn ($promotionQuery) => $this->activePromotions($promotionQuery)->orderBy('discount_value'),
                ])
                ->withMin(['schedules as next_departure_date' => fn ($scheduleQuery) => $scheduleQuery
                    ->where('status', 'open')
                    ->whereDate('departure_date', '>=', today())], 'departure_date')
                ->orderByPivot('sort_order')
                ->orderBy('tours.sort_order'),
        ]);

        $page = [
            'name' => $productLine->name,
            'slug' => $productLine->slug,
            'kicker' => $productLine->kicker,
            'summary' => $productLine->summary,
            'description' => $productLine->description ?: $productLine->summary,
            'benefits' => $productLine->benefits ?: [],
            'icon' => $productLine->icon ?: 'bi-compass',
            'hero_image' => $this->imageUrl($productLine->hero_image),
            'cover_image' => $this->imageUrl($productLine->cover_image),
            'seo_title' => $productLine->seo_title ?: $productLine->name,
            'seo_description' => $productLine->seo_description ?: $productLine->summary,
            'tours' => $productLine->tours
                ->map(fn ($tour): array => $this->tourCardPresenter->present($tour, $tour->promotions->first()))
                ->all(),
            'services' => $productLine->services->map(fn ($service): array => [
                'title' => $service->name,
                'slug' => $service->slug,
                'icon' => $service->icon ?: 'bi-check2-circle',
                'category' => $service->category?->name,
                'description' => $service->description,
            ])->all(),
        ];

        return view('frontend.product_lines.show', [
            'productLine' => $page,
            'structuredData' => $this->structuredData->encode($this->structuredData->productLine($page)),
        ]);
    }

    private function publishedTours(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->where('tours.is_active', true)
            ->where('tours.status', 'published')
            ->where(fn (Builder $publishedQuery) => $publishedQuery->whereNull('tours.published_at')->orWhere('tours.published_at', '<=', now()));
    }

    private function activePromotions(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($dateQuery) => $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    private function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')
            ? $path
            : asset($path);
    }
}
