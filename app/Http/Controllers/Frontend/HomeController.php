<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\SiteAsset;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TravelMoment;
use App\Services\DestinationTreeService;
use App\Services\MediaReferenceService;
use App\Services\SiteSettingsService;
use App\Services\StructuredDataService;
use App\Services\TravelServiceCatalog;
use App\Settings\WebsiteSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(
        SiteSettingsService $siteSettings,
        StructuredDataService $structuredData,
        DestinationTreeService $destinationTree,
        TravelServiceCatalog $serviceCatalog,
    ): View {
        $settings = $siteSettings->general();
        $mediaSettings = $siteSettings->media();
        $sliderItems = Slider::query()
            ->where('key', 'home')
            ->where('is_active', true)
            ->with(['items' => fn ($query) => $query->where('is_active', true)])
            ->first()?->items ?? collect();

        $siteAsset = SiteAsset::query()->where('key', 'site')->first();
        $homepageHeroUrl = $this->imageUrl($mediaSettings->homepage_hero_url)
            ?: $this->mediaUrl($siteAsset, 'homepage_hero');
        $heroSlides = $sliderItems->map(fn ($slide): array => [
            'eyebrow' => 'Mỗi hành trình, một câu chuyện',
            'title' => $slide->title ?: 'Chạm vào những miền đất đáng nhớ',
            'description' => $slide->subtitle ?: 'Khám phá thế giới theo cách riêng của bạn với những hành trình được thiết kế chỉn chu, chân thành và đầy cảm hứng.',
            'image_url' => $this->imageUrl($slide->image_path) ?: $homepageHeroUrl,
            'button_label' => $slide->button_label ?: 'Khám phá hành trình',
            'button_url' => $this->linkUrl($slide->button_url ?: '/tours'),
        ])->values()->all();

        $heroFallback = [
            'eyebrow' => 'Mỗi hành trình, một câu chuyện',
            'title' => 'Chạm vào những miền đất đáng nhớ',
            'description' => 'Khám phá thế giới theo cách riêng của bạn với những hành trình được thiết kế chỉn chu, chân thành và đầy cảm hứng.',
            'image_url' => $homepageHeroUrl,
            'button_label' => 'Khám phá hành trình',
            'button_url' => route('tours.index'),
        ];

        $destinationNodes = $destinationTree->activeNodes();
        $publishedDestinationTours = $this->publishedTours()
            ->with('destinations:id,parent_id')
            ->get(['id']);
        $destinationCounts = $destinationTree->publishedTourCounts($destinationNodes, $publishedDestinationTours);
        $featuredDestinations = $this->destinations($destinationTree, $destinationNodes, $destinationCounts);
        $tourTypes = $this->tourTypes();
        $featuredTours = $this->featuredTours();
        $promotionalTours = $this->promotionalTours();
        $customerGallery = $this->customerGallery();
        $aboutImageUrl = $this->imageUrl($mediaSettings->about_image_url)
            ?: $this->mediaUrl($siteAsset, 'about_image');
        $brandImageUrl = $aboutImageUrl
            ?: $homepageHeroUrl
            ?: ($featuredTours[0]['image_url'] ?? null);
        $promotionFallbackUrl = collect($promotionalTours)->pluck('image_url')->filter()->first()
            ?: $aboutImageUrl
            ?: $homepageHeroUrl;
        $promotionBackdropUrl = is_file(public_path('images/promo-coastal-sunset.png'))
            ? asset('images/promo-coastal-sunset.png')
            : $promotionFallbackUrl;
        $impactBackdropUrl = $aboutImageUrl ?: $homepageHeroUrl ?: $promotionBackdropUrl;
        $customTourBackdropUrl = $aboutImageUrl
            ?: $homepageHeroUrl
            ?: $promotionBackdropUrl
            ?: ($customerGallery[3]['url'] ?? null);

        $websiteSettings = $siteSettings->website();

        return view('frontend.home', [
            'heroSlides' => $heroSlides,
            'heroFallback' => $heroFallback,
            'aboutImageUrl' => $aboutImageUrl,
            'brandImageUrl' => $brandImageUrl,
            'brandIntroduction' => $this->brandIntroduction($websiteSettings),
            'tourTypes' => $tourTypes,
            'serviceCategories' => $serviceCatalog->homeCards(),
            'featuredTours' => $featuredTours,
            'promotionalTours' => $promotionalTours,
            'promotionTitle' => $this->promotionTitle($promotionalTours),
            'promotionBackdropUrl' => $promotionBackdropUrl,
            'impactBackdropUrl' => $impactBackdropUrl,
            'featuredDestinations' => $featuredDestinations,
            'destinationOptions' => $destinationTree->optionsWithTours($destinationNodes, $destinationCounts),
            'destinationTabs' => $this->destinationTabs($featuredDestinations),
            'customTourBackdropUrl' => $customTourBackdropUrl,
            'impactTitle' => $websiteSettings->impact_title,
            'impactStats' => $siteSettings->impactStats(),
            'customerGallery' => $customerGallery,
            'partners' => $this->partners($websiteSettings),
            'customTourContent' => [
                'title' => $websiteSettings->custom_tour_title,
                'description' => $websiteSettings->custom_tour_description,
            ],
            'latestPosts' => $this->posts(),
            'testimonials' => Testimonial::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(3)
                ->get()
                ->map(fn (Testimonial $testimonial): array => [
                    'name' => $testimonial->customer_name,
                    'title' => $testimonial->customer_title,
                    'content' => $testimonial->content,
                    'rating' => min(5, max(1, (int) $testimonial->rating)),
                    'avatar_url' => $this->imageUrl($testimonial->avatar_path),
                    'avatar_initials' => $this->avatarInitials($testimonial->customer_name),
                ])
                ->all(),
            'structuredData' => $structuredData->encode($structuredData->home($settings)),
            'stats' => [
                'destinations' => collect($destinationCounts)->filter(fn (int $count): bool => $count > 0)->count(),
                'tours' => $this->publishedTours()->count(),
                'posts' => Post::query()->where('is_active', true)->count(),
            ],
        ]);
    }

    private function brandIntroduction(WebsiteSettings $settings): array
    {
        return [
            'title' => $settings->about_title,
            'paragraphs' => [
                $settings->about_paragraph_one,
                $settings->about_paragraph_two,
            ],
            'highlights' => [
                ['number' => '01', 'title' => 'Thiết kế riêng', 'description' => 'Lịch trình bắt đầu từ nhu cầu thực tế, không từ một khuôn mẫu có sẵn.'],
                ['number' => '02', 'title' => 'Vận hành chỉn chu', 'description' => 'Dịch vụ, thời gian và đầu mối phối hợp được làm rõ trước khi khởi hành.'],
                ['number' => '03', 'title' => 'Đồng hành xuyên suốt', 'description' => 'Hỗ trợ trước, trong và sau chuyến đi để khách hàng luôn an tâm.'],
            ],
        ];
    }

    private function customerGallery(): array
    {
        return TravelMoment::query()
            ->where('is_active', true)
            ->whereHas('group', fn (Builder $query) => $query->where('is_active', true))
            ->with('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (TravelMoment $moment): array => [
                'url' => $this->imageUrl($moment->image_url),
                'alt' => $moment->alt_text ?: $moment->title,
                'title' => $moment->title,
                'caption' => $moment->caption,
                'group_slug' => $moment->group->slug,
                'group_name' => $moment->group->name,
            ])
            ->filter(fn (array $moment): bool => filled($moment['url']))
            ->values()
            ->all();
    }

    private function partners(WebsiteSettings $settings): array
    {
        return collect(preg_split('/\R/u', (string) $settings->partner_names))
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->values()
            ->all();
    }

    private function featuredTours(): array
    {
        return $this->withNextDeparture($this->publishedTours())
            ->where(fn (Builder $query) => $query->where('is_featured', true)
                ->orWhere(fn (Builder $fallback) => $fallback->where('booking_open', true)
                    ->whereHas('schedules', fn (Builder $schedule) => $this->availableSchedule($schedule))))
            ->with(['destinations', 'categories', 'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order')])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(3)
            ->get()
            ->map(fn (Tour $tour): array => $this->tourCard($tour))
            ->all();
    }

    private function tourTypes(): array
    {
        return TourCategory::query()
            ->where('is_active', true)
            ->where('is_home', true)
            ->withCount(['tours' => fn (Builder $query) => $this->publishedTours($query)])
            ->with(['tours' => fn ($query) => $this->publishedTours($query)
                ->with(['images' => fn ($imageQuery) => $imageQuery->orderByDesc('is_cover')->orderBy('sort_order')])])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (TourCategory $category): array {
                $tourCover = $category->tours->first()?->images->first()?->path;

                return [
                    'title' => $category->name,
                    'description' => $category->description,
                    'detail' => $category->tours_count.' hành trình',
                    'cover_image_url' => $this->imageUrl($category->cover_image)
                        ?: $this->mediaUrl($category, 'cover')
                        ?: $this->imageUrl($tourCover),
                    'url' => route('tours.category', ['category' => $category->slug]),
                ];
            })
            ->values()
            ->all();
    }

    private function promotionalTours(): array
    {
        $query = $this->withNextDeparture($this->publishedTours())
            ->where('booking_open', true)
            ->whereHas('schedules', fn (Builder $query) => $this->availableSchedule($query))
            ->with([
                'destinations',
                'categories',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
                'promotions' => fn ($query) => $this->activePromotion($query)->orderBy('discount_value'),
            ])
            ->orderBy('next_departure_date')
            ->orderBy('sort_order')
            ->orderBy('id');

        $tours = (clone $query)
            ->whereHas('promotions', fn (Builder $query) => $this->activePromotion($query))
            ->limit(6)
            ->get();

        if ($tours->count() < 3) {
            $tours = $tours->concat((clone $query)
                ->whereNotIn('id', $tours->modelKeys())
                ->limit(3 - $tours->count())
                ->get());
        }

        return $tours
            ->map(fn (Tour $tour): array => $this->tourCard($tour, $tour->promotions->first()))
            ->values()
            ->all();
    }

    private function promotionTitle(array $tours): string
    {
        $discounted = collect($tours)->filter(fn (array $tour): bool => (int) $tour['discount_percent'] > 0)->count();

        if ($discounted === 0) {
            return 'Hành trình sắp khởi hành';
        }

        return $discounted === count($tours) ? 'Ưu đãi đang diễn ra' : 'Ưu đãi & khởi hành sắp tới';
    }

    private function destinations(DestinationTreeService $destinationTree, Collection $nodes, array $counts): array
    {
        return $nodes
            ->filter(fn (Destination $destination): bool => ($counts[(int) $destination->getKey()] ?? 0) > 0)
            ->map(function (Destination $destination) use ($destinationTree, $nodes, $counts): array {
                $tab = $this->destinationTab($destination, $destinationTree, $nodes);

                return [
                    'name' => $destination->name,
                    'slug' => $destination->slug,
                    'url' => $destination->landing_enabled
                        ? route('destinations.show', ['destination' => $destination->slug])
                        : route('tours.index', ['destination' => $destination->slug]),
                    'summary' => $destination->summary,
                    'image_url' => $this->destinationImage($destination, $destinationTree, $nodes),
                    'tour_count' => $counts[(int) $destination->getKey()] ?? 0,
                    'tab_key' => $tab['key'],
                    'tab_label' => $tab['label'],
                    'tab_order' => $tab['order'],
                    'featured_order' => $destination->is_featured ? 0 : 1,
                    'sort_order' => $destination->sort_order,
                ];
            })
            ->sortBy([
                ['tab_order', 'asc'],
                ['featured_order', 'asc'],
                ['sort_order', 'asc'],
                ['name', 'asc'],
            ])
            ->groupBy('tab_key')
            ->map(fn ($destinations) => $destinations->take(8))
            ->flatten(1)
            ->values()
            ->all();
    }

    /** @param list<array<string, mixed>> $destinations @return list<array{key: string, label: string}> */
    private function destinationTabs(array $destinations): array
    {
        return collect($destinations)
            ->groupBy('tab_key')
            ->sortBy(fn ($items) => $items->first()['tab_order'])
            ->map(fn ($items, string $key): array => [
                'key' => $key,
                'label' => $items->first()['tab_label'],
            ])
            ->values()
            ->all();
    }

    /** @return array{key: string, label: string, order: int} */
    private function destinationTab(Destination $destination, DestinationTreeService $destinationTree, Collection $nodes): array
    {
        $root = $destinationTree->root($destination, $nodes);

        if ($root->slug === 'viet-nam') {
            return ['key' => 'noi-dia', 'label' => 'Nội địa', 'order' => 99];
        }

        return [
            'key' => $root->slug ?: Str::slug($root->name),
            'label' => $root->name ?: 'Điểm đến khác',
            'order' => (int) $root->sort_order,
        ];
    }

    private function destinationImage(Destination $destination, DestinationTreeService $destinationTree, Collection $nodes): ?string
    {
        $image = $this->imageUrl($destination->cover_image) ?: $this->mediaUrl($destination, 'cover');

        if ($image) {
            return $image;
        }

        foreach ($destinationTree->descendantIds($destination, $nodes) as $id) {
            $node = $nodes->firstWhere('id', $id);

            if (! $node || (int) $node->getKey() === (int) $destination->getKey()) {
                continue;
            }

            $image = $this->imageUrl($node->cover_image) ?: $this->mediaUrl($node, 'cover');

            if ($image) {
                return $image;
            }
        }

        return null;
    }

    private function avatarInitials(?string $name): string
    {
        return Str::of((string) $name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(-2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('') ?: 'HG';
    }

    private function posts(): array
    {
        return Post::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->with('category')
            ->latest('published_at')
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(fn (Post $post): array => [
                'name' => $post->name,
                'slug' => $post->slug,
                'summary' => $post->summary,
                'category' => $post->category?->name,
                'published_at' => $post->published_at?->translatedFormat('d/m/Y'),
                'image_url' => $this->imageUrl($post->cover_image),
            ])
            ->all();
    }

    private function publishedTours(Builder|Relation|null $query = null): Builder|Relation
    {
        return ($query ?: Tour::query())
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn (Builder $publishedQuery) => $publishedQuery->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function activePromotion(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $dateQuery) => $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $dateQuery) => $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    private function availableSchedule(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->where('status', 'open')
            ->whereDate('departure_date', '>=', today())
            ->where(function (Builder $seatQuery): void {
                $seatQuery
                    ->where('seats_total', 0)
                    ->orWhereColumn('seats_reserved', '<', 'seats_total');
            });
    }

    private function tourCard(Tour $tour, ?Promotion $promotion = null): array
    {
        $price = (float) $tour->starting_price;
        $salePrice = $this->salePrice($price, $promotion);
        $discountPercent = $this->discountPercent($price, $promotion);
        $nextSchedule = $this->nextSchedule($tour);
        $nextDeparture = $nextSchedule?->departure_date
            ?: (filled($tour->getAttribute('next_departure_date'))
                ? Carbon::parse($tour->getAttribute('next_departure_date'))
                : null);

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
            'duration' => $tour->duration_days.' ngày'.($tour->duration_nights ? ' '.$tour->duration_nights.' đêm' : ''),
            'duration_compact' => (int) $tour->duration_days.'N'.((int) $tour->duration_nights > 0 ? (int) $tour->duration_nights.'Đ' : ''),
            'transport' => $tour->transport ?: 'Theo chương trình',
            'next_departure' => $nextDeparture?->format('d/m/Y'),
            'next_departure_at' => $nextDeparture?->copy()->startOfDay()->toIso8601String(),
            'countdown_label' => $this->countdownLabel($nextDeparture),
            'seats_left' => $nextSchedule?->seatsLeft(),
            'seats_label' => $nextSchedule?->slotLabel(),
            'departure_dates' => $tour->relationLoaded('schedules')
                ? $tour->getRelation('schedules')->pluck('departure_date')->filter()->map(fn ($date): string => $date->format('d/m'))->values()->all()
                : [],
            'price' => $price,
            'price_label' => $this->moneyLabel($price, $tour->currency),
            'sale_price_label' => $discountPercent ? $this->moneyLabel($salePrice, $tour->currency) : null,
            'discount_percent' => $discountPercent,
            'currency' => $tour->currency ?: 'VND',
            'destination' => $destinations->pluck('name')->filter()->implode(' · '),
            'category' => $categories->pluck('name')->filter()->implode(' · '),
            'category_slug' => $categories->first()?->slug,
            'image_url' => $this->imageUrl($tour->images->first()?->path)
                ?: $this->imageUrl($primaryDestination?->cover_image)
                ?: $this->mediaUrl($primaryDestination, 'cover'),
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

    private function moneyLabel(float|string|null $amount, ?string $currency): string
    {
        return (float) $amount > 0 ? number_format((float) $amount, 0, ',', '.').'đ' : 'Liên hệ';
    }

    private function countdownLabel(?Carbon $departure): ?string
    {
        if (! $departure) {
            return null;
        }

        $totalSeconds = max(0, (int) now()->diffInSeconds($departure, false));

        if ($totalSeconds === 0) {
            return 'Đang khởi hành';
        }

        $days = intdiv($totalSeconds, 86400);
        $hours = intdiv($totalSeconds % 86400, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        return $days > 0
            ? $days.' ngày '.sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function withNextDeparture(Builder $query): Builder
    {
        return $query
            ->withMin([
                'schedules as next_departure_date' => fn (Builder $scheduleQuery) => $this->availableSchedule($scheduleQuery),
            ], 'departure_date')
            ->with(['schedules' => fn (Builder|Relation $scheduleQuery) => $this->availableSchedule($scheduleQuery)->orderBy('departure_date')->orderBy('id')]);
    }

    private function nextSchedule(Tour $tour): ?object
    {
        if (! $tour->relationLoaded('schedules')) {
            return null;
        }

        return $tour->getRelation('schedules')->first();
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

    private function linkUrl(?string $path): string
    {
        if (blank($path)) {
            return route('tours.index');
        }

        return Str::startsWith($path, ['http://', 'https://', '#', '/', 'mailto:', 'tel:']) ? $path : url($path);
    }
}
