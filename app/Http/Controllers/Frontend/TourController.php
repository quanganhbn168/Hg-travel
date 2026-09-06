<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Promotion;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourSchedule;
use App\Services\DestinationTreeService;
use App\Services\MediaReferenceService;
use App\Services\SiteSettingsService;
use App\Services\StructuredDataService;
use App\Settings\TourSettings;
use App\Support\TourContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TourController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $siteSettings,
        private readonly StructuredDataService $structuredData,
        private readonly TourSettings $tourSettings,
        private readonly DestinationTreeService $destinationTree,
    ) {}

    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function category(Request $request, TourCategory $category): View|RedirectResponse
    {
        if (! $category->is_active) {
            return redirect()->route('tours.index', array_filter([
                'scope' => $this->legacyCategoryScope($category->slug),
            ]), 301);
        }

        return $this->listing($request, $category);
    }

    public function show(Tour $tour): View
    {
        abort_unless($this->isPublished($tour), 404);

        $tour->load([
            'categories',
            'destinations',
            'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
            'itineraries',
            'sections',
            'inclusions',
            'schedules' => fn ($query) => $query
                ->when(! $this->tourSettings->show_schedules, fn ($scheduleQuery) => $scheduleQuery->whereRaw('1 = 0'))
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->orderBy('departure_date'),
            'reviews' => fn ($query) => $query
                ->where('status', 'approved')
                ->with('user')
                ->latest(),
        ]);

        $tourData = $this->detailData($tour);
        $relatedTours = $this->withNextDeparture($this->publishedQuery())
            ->whereKeyNot($tour->getKey())
            ->when($tour->categories->isNotEmpty(), fn (Builder $query) => $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereIn('tour_categories.id', $tour->categories->modelKeys())))
            ->with([
                'categories',
                'destinations',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
                'promotions' => fn ($query) => $this->activePromotions($query)->orderBy('discount_value'),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(3)
            ->get()
            ->map(fn (Tour $relatedTour): array => $this->cardData($relatedTour))
            ->all();

        return view('frontend.tours.show', [
            'tour' => $tourData,
            'relatedTours' => $relatedTours,
            'structuredData' => $this->structuredData->encode($this->structuredData->tourDetail(
                $tourData,
                $this->siteSettings->general()->site_name,
            )),
        ]);
    }

    private function listing(Request $request, ?TourCategory $activeCategory = null): View
    {
        $filters = $this->filters($request);
        $query = $this->withNextDeparture($this->publishedQuery())->with([
            'categories',
            'destinations',
            'images' => fn ($imageQuery) => $imageQuery->orderByDesc('is_cover')->orderBy('sort_order'),
            'promotions' => fn ($promotionQuery) => $this->activePromotions($promotionQuery)->orderBy('discount_value'),
        ]);

        $selectedCategory = $activeCategory ?: ($filters['category'] !== ''
            ? TourCategory::query()->where('is_active', true)->where('slug', $filters['category'])->first()
            : null);

        if ($selectedCategory) {
            $this->applyCategoryScope($query, $selectedCategory);
            $filters['category'] = $selectedCategory->slug;
        } elseif ($filters['category'] !== '') {
            $query->whereRaw('1 = 0');
        }

        if ($filters['q'] !== '') {
            $search = '%'.$filters['q'].'%';
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhere('summary', 'like', $search)
                    ->orWhereHas('destinations', fn (Builder $destinationQuery) => $destinationQuery->where('name', 'like', $search));
            });
        }

        if ($filters['destination'] !== '') {
            $selectedDestination = Destination::query()
                ->where('is_active', true)
                ->where('slug', $filters['destination'])
                ->first();

            if ($selectedDestination) {
                $query->whereHas('destinations', fn (Builder $destinationQuery) => $destinationQuery->whereIn('destinations.id', $this->destinationTree->descendantIds($selectedDestination)));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($filters['scope'] !== '') {
            $this->applyDestinationScope($query, $filters['scope']);
        }

        match ($filters['duration']) {
            '1-3' => $query->whereBetween('duration_days', [1, 3]),
            '4-7' => $query->whereBetween('duration_days', [4, 7]),
            '8+' => $query->where('duration_days', '>=', 8),
            default => null,
        };

        match ($filters['budget']) {
            'under-5' => $query->where('starting_price', '<', 5000000),
            '5-10' => $query->whereBetween('starting_price', [5000000, 10000000]),
            'over-10' => $query->where('starting_price', '>', 10000000),
            default => null,
        };

        match ($filters['sort']) {
            'newest' => $query->latest('published_at')->latest('id'),
            'price_asc' => $query->orderBy('starting_price')->orderBy('sort_order'),
            'price_desc' => $query->orderByDesc('starting_price')->orderBy('sort_order'),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->latest('id'),
        };

        $tours = $query->paginate(9)->withQueryString();
        $tours->setCollection($tours->getCollection()->map(fn (Tour $tour): array => $this->cardData($tour)));

        $categoryTree = $this->categoryNavigation($filters['scope']);
        $categories = $this->flattenCategoryNavigation($categoryTree);

        $destinations = $this->destinationOptions($filters['scope']);

        $scopeTitle = match ($filters['scope']) {
            'domestic' => 'Tour trong nước',
            'international' => 'Tour nước ngoài',
            default => null,
        };

        $page = [
            'title' => $activeCategory ? 'Tour '.$activeCategory->name : ($scopeTitle ?: 'Tour du lịch'),
            'description' => $activeCategory?->seo_description ?: 'Khám phá những hành trình được tuyển chọn kỹ lưỡng, rõ lịch trình và phù hợp với cách bạn muốn tận hưởng chuyến đi.',
            'eyebrow' => $activeCategory ? 'Loại hình tour' : 'Khám phá hành trình',
        ];

        return view('frontend.tours.index', [
            'tours' => $tours,
            'categories' => $categories,
            'categoryTree' => $categoryTree,
            'destinations' => $destinations,
            'filters' => $filters,
            'page' => $page,
            'activeCategory' => $activeCategory ? [
                'name' => $activeCategory->name,
                'slug' => $activeCategory->slug,
                'description' => $activeCategory->description,
            ] : null,
            'structuredData' => $this->structuredData->encode($this->structuredData->tourListing($page, $tours, $activeCategory ? [
                'name' => $activeCategory->name,
                'slug' => $activeCategory->slug,
            ] : null)),
        ]);
    }

    private function publishedQuery(): Builder
    {
        return $this->publishedScope(Tour::query());
    }

    private function publishedScope(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn (Builder $publishedQuery) => $publishedQuery->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function activePromotions($query)
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($dateQuery) => $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    private function isPublished(Tour $tour): bool
    {
        return $tour->is_active
            && $tour->status === 'published'
            && ($tour->published_at === null || $tour->published_at->isPast());
    }

    private function withNextDeparture(Builder $query): Builder
    {
        return $query->withMin([
            'schedules as next_departure_date' => fn (Builder $scheduleQuery) => $scheduleQuery
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today()),
        ], 'departure_date');
    }

    private function filters(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));

        return [
            'q' => mb_strlen($search) > 100 ? mb_substr($search, 0, 100) : $search,
            'category' => filled($request->query('category')) ? Str::slug((string) $request->query('category')) : '',
            'destination' => filled($request->query('destination')) ? Str::slug((string) $request->query('destination')) : '',
            'scope' => in_array($request->query('scope'), ['domestic', 'international'], true) ? $request->query('scope') : '',
            'duration' => in_array($request->query('duration'), ['1-3', '4-7', '8+'], true) ? $request->query('duration') : '',
            'budget' => in_array($request->query('budget'), ['under-5', '5-10', 'over-10'], true) ? $request->query('budget') : '',
            'sort' => in_array($request->query('sort'), ['featured', 'newest', 'price_asc', 'price_desc'], true) ? $request->query('sort') : 'featured',
        ];
    }

    private function destinationOptions(string $scope = ''): array
    {
        $destinations = $this->destinationTree->activeNodes();
        $publishedTours = $this->publishedQuery()
            ->when($scope !== '', fn (Builder $query) => $this->applyDestinationScope($query, $scope))
            ->with('destinations:id,parent_id')
            ->get(['id']);
        $counts = $this->destinationTree->publishedTourCounts($destinations, $publishedTours);

        return $this->destinationTree->optionsWithTours($destinations, $counts, $scope !== '' ? $scope : null);
    }

    private function categoryNavigation(string $scope = ''): array
    {
        $categories = TourCategory::query()
            ->where('is_active', true)
            ->withCount(['tours' => function (Builder $tourQuery) use ($scope): void {
                $this->publishedScope($tourQuery);

                if ($scope !== '') {
                    $this->applyDestinationScope($tourQuery, $scope);
                }
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug']);

        $byParent = $categories->groupBy(fn (TourCategory $category): string => (string) ($category->parent_id ?? 0));
        $buildTree = function (int $parentId) use (&$buildTree, $byParent): array {
            return collect($byParent->get((string) $parentId, []))
                ->map(function (TourCategory $category) use (&$buildTree): ?array {
                    $children = $buildTree((int) $category->getKey());
                    $tourCount = (int) $category->tours_count + array_sum(array_column($children, 'tour_count'));

                    if ($tourCount === 0) {
                        return null;
                    }

                    return [
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'tour_count' => $tourCount,
                        'children' => $children,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        };

        return $buildTree(0);
    }

    private function flattenCategoryNavigation(array $nodes, int $depth = 0): array
    {
        return collect($nodes)
            ->flatMap(function (array $node) use ($depth): array {
                return [[
                    'name' => str_repeat('— ', $depth).$node['name'],
                    'slug' => $node['slug'],
                    'tour_count' => $node['tour_count'],
                ], ...$this->flattenCategoryNavigation($node['children'], $depth + 1)];
            })
            ->values()
            ->all();
    }

    private function applyCategoryScope(Builder $query, TourCategory $category): void
    {
        $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereIn('tour_categories.id', $this->categoryBranchIds($category)));
    }

    private function categoryBranchIds(TourCategory $category): array
    {
        $ids = [(int) $category->getKey()];
        $parentIds = $ids;

        while ($parentIds !== []) {
            $childIds = TourCategory::query()
                ->where('is_active', true)
                ->whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $parentIds = array_values(array_diff($childIds, $ids));
            $ids = [...$ids, ...$parentIds];
        }

        return $ids;
    }

    private function legacyCategoryScope(string $slug): ?string
    {
        if ($slug === 'tour-trong-nuoc' || str_starts_with($slug, 'tour-mien-')) {
            return 'domestic';
        }

        if ($slug === 'tour-nuoc-ngoai' || str_starts_with($slug, 'tour-chau-') || in_array($slug, ['tour-nhat-ban', 'tour-han-quoc'], true)) {
            return 'international';
        }

        return null;
    }

    private function applyDestinationScope(Builder $query, string $scope): void
    {
        if (! in_array($scope, ['domestic', 'international'], true)) {
            return;
        }

        $query->whereHas('destinations', fn (Builder $destinationQuery) => $destinationQuery->where('destinations.market', $scope));
    }

    private function cardData(Tour $tour, ?Promotion $promotion = null): array
    {
        $promotion ??= $tour->relationLoaded('promotions') ? $tour->promotions->first() : null;
        $price = (float) $tour->starting_price;
        $discountPercent = $this->discountPercent($price, $promotion);

        $destinations = $tour->relationLoaded('destinations')
            ? $tour->getRelation('destinations')
            : $tour->destinations()->get();
        $primaryDestination = $destinations->first();
        $categories = $tour->relationLoaded('categories')
            ? $tour->getRelation('categories')
            : $tour->categories()->get();

        return [
            'id' => $tour->getKey(),
            'name' => $tour->name,
            'slug' => $tour->slug,
            'booking_open' => (bool) $tour->booking_open,
            'summary' => $tour->summary,
            'image_url' => $this->tourImageUrl($tour),
            'category' => $categories->pluck('name')->filter()->implode(' · '),
            'category_slug' => $categories->first()?->slug,
            'categories' => $categories->map(fn (TourCategory $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values()->all(),
            'destination' => $destinations->pluck('name')->filter()->implode(' · '),
            'destination_slug' => $primaryDestination?->slug,
            'destinations' => $destinations->map(fn (Destination $destination): array => [
                'name' => $destination->name,
                'slug' => $destination->slug,
            ])->values()->all(),
            'duration' => $this->durationLabel((int) $tour->duration_days, (int) $tour->duration_nights),
            'duration_days' => (int) $tour->duration_days,
            'transport' => $tour->transport ?: 'Theo chương trình',
            'next_departure' => filled($tour->getAttribute('next_departure_date'))
                ? Carbon::parse($tour->getAttribute('next_departure_date'))->format('d/m/Y')
                : null,
            'price' => $price,
            'price_label' => $this->moneyLabel($tour->starting_price, $tour->currency),
            'sale_price_label' => $discountPercent ? $this->moneyLabel($this->salePrice($price, $promotion), $tour->currency) : null,
            'discount_percent' => $discountPercent,
            'currency' => $tour->currency,
        ];
    }

    private function salePrice(float $price, ?Promotion $promotion): float
    {
        if (! $promotion) {
            return $price;
        }

        return $promotion->discount_type === 'percentage'
            ? max(0, $price * (1 - ((float) $promotion->discount_value / 100)))
            : max(0, $price - (float) $promotion->discount_value);
    }

    private function discountPercent(float $price, ?Promotion $promotion): ?int
    {
        if (! $promotion || $price <= 0) {
            return null;
        }

        $percent = $promotion->discount_type === 'percentage'
            ? (float) $promotion->discount_value
            : ((float) $promotion->discount_value / $price) * 100;

        return min(100, max(1, (int) round($percent)));
    }

    private function detailData(Tour $tour): array
    {
        $gallery = collect();
        $mediaUrl = $this->mediaUrl($tour, 'tour_images');

        if ($mediaUrl) {
            $gallery->push(['url' => $mediaUrl, 'alt' => $tour->name]);
        }

        foreach ($tour->images as $image) {
            $url = $this->imageUrl($image->path);

            if ($url) {
                $gallery->push(['url' => $url, 'alt' => $image->alt_text ?: $tour->name]);
            }
        }

        $card = $this->cardData($tour);
        $card['next_departure'] = $tour->schedules->first()?->departure_date?->format('d/m/Y');
        $reviewCount = $tour->reviews->count();
        $sections = $tour->sections
            ->filter(fn ($section): bool => filled($section->title) || filled($section->content))
            ->map(fn ($section): array => [
                'type' => $section->type,
                'title' => $section->title ?: (TourContent::sectionTypes()[$section->type] ?? 'Thông tin khác'),
                'content' => TourContent::html($section->content),
            ]);

        return $card + [
            'code' => $tour->code,
            'description' => TourContent::html($tour->description ?: $tour->summary),
            'banner_image_url' => $this->imageUrl($tour->banner_image),
            'seo_title' => $tour->seo_title,
            'seo_description' => $tour->seo_description ?: $tour->summary,
            'max_guests' => $tour->max_guests,
            'booking_open' => (bool) $tour->booking_open,
            'review_count' => $reviewCount,
            'average_rating' => $reviewCount > 0 ? round((float) $tour->reviews->avg('rating'), 1) : null,
            'gallery' => $gallery->unique('url')->values()->all(),
            'itineraries' => $tour->itineraries->map(fn ($itinerary): array => [
                'day_number' => $itinerary->day_number,
                'title' => $itinerary->title,
                'description' => TourContent::html($itinerary->description),
                'meals' => $itinerary->meals,
                'accommodation' => $itinerary->accommodation,
                'meal_lines' => TourContent::lines($itinerary->meals),
                'accommodation_lines' => TourContent::lines($itinerary->accommodation),
            ])->values()->all(),
            'highlights' => $sections->where('type', 'highlights')->values()->all(),
            'sections' => $sections->where('type', '!=', 'highlights')->values()->all(),
            'inclusion_groups' => $tour->inclusions->groupBy('type')->map(fn ($items, $type): array => [
                'title' => $type === 'excluded' ? 'Giá tour không bao gồm' : 'Giá tour bao gồm',
                'type' => $type,
                'items' => $items->pluck('content')->all(),
            ])->sortBy(fn ($group) => $group['type'] === 'excluded' ? 1 : 0)->values()->all(),
            'inclusions' => $tour->inclusions->map(fn ($inclusion): array => [
                'type' => $inclusion->type,
                'content' => $inclusion->content,
            ])->values()->all(),
            'schedules' => $tour->schedules->map(fn (TourSchedule $schedule): array => $this->scheduleData($schedule, $tour))->values()->all(),
            'show_seat_availability' => (bool) $this->tourSettings->show_seat_availability,
            'schedule_note' => $this->tourSettings->schedule_note,
            'reviews' => $tour->reviews->map(fn ($review): array => [
                'name' => $review->user?->name ?: 'Khách hàng',
                'rating' => min(5, max(1, (int) $review->rating)),
                'title' => $review->title,
                'content' => $review->content,
            ])->values()->all(),
        ];
    }

    private function scheduleData(TourSchedule $schedule, Tour $tour): array
    {
        $seatsLeft = $schedule->seatsLeft();
        $regularPrice = (float) $schedule->price;
        $salePrice = (float) $schedule->sale_price;
        $hasSalePrice = $salePrice > 0 && ($regularPrice <= 0 || $salePrice < $regularPrice);
        $effectivePrice = $schedule->effectivePrice();

        return [
            'id' => $schedule->getKey(),
            'departure_date' => $schedule->departure_date?->format('d/m/Y'),
            'departure_date_value' => $schedule->departure_date?->format('Y-m-d'),
            'return_date' => $schedule->return_date?->format('d/m/Y'),
            'seats_total' => (int) $schedule->seats_total > 0 ? (int) $schedule->seats_total : null,
            'seats_reserved' => max(0, (int) $schedule->seats_reserved),
            'seats_left' => $seatsLeft,
            'slot_label' => $schedule->slotLabel(),
            'slot_class' => $seatsLeft === null ? 'is-unknown' : ($seatsLeft <= 3 ? 'is-limited' : 'is-available'),
            'is_available' => $schedule->isAvailable(),
            'price_label' => $this->moneyLabel($effectivePrice, $tour->currency),
            'regular_price_label' => $hasSalePrice ? $this->moneyLabel($regularPrice, $tour->currency) : null,
            'sale_price_label' => $hasSalePrice ? $this->moneyLabel($salePrice, $tour->currency) : null,
            'has_sale_price' => $hasSalePrice,
            'notes' => $schedule->notes,
        ];
    }

    private function durationLabel(int $days, int $nights): string
    {
        return $days.' ngày'.($nights > 0 ? ' '.$nights.' đêm' : '');
    }

    private function moneyLabel(float|string|null $amount, ?string $currency): string
    {
        return (float) $amount > 0
            ? number_format((float) $amount, 0, ',', '.').'đ'
            : 'Liên hệ';
    }

    private function tourImageUrl(Tour $tour): ?string
    {
        $mediaUrl = $this->mediaUrl($tour, 'tour_images');

        if ($mediaUrl) {
            return $mediaUrl;
        }

        return $this->imageUrl($tour->images->first()?->path);
    }

    private function mediaUrl(?object $model, string $collection): ?string
    {
        if (! $model || ! method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl($collection) ?: null;
    }

    private function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return app(MediaReferenceService::class)->url($path);
    }
}
