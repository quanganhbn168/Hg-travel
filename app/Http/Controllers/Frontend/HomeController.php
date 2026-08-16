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
use App\Services\SiteSettingsService;
use App\Services\ProductLineService;
use App\Services\StructuredDataService;
use App\Services\TravelServiceCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(SiteSettingsService $siteSettings, StructuredDataService $structuredData, ProductLineService $productLineService, TravelServiceCatalog $serviceCatalog): View
    {
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

        $featuredDestinations = $this->destinations();
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
            'featuredProducts' => $productLineService->homeCards(),
            'serviceCategories' => $serviceCatalog->homeCards(),
            'featuredTours' => $featuredTours,
            'promotionalTours' => $promotionalTours,
            'promotionBackdropUrl' => $promotionBackdropUrl,
            'impactBackdropUrl' => $impactBackdropUrl,
            'featuredDestinations' => $featuredDestinations,
            'destinationOptions' => Destination::query()
                ->where('is_active', true)
                ->whereHas('tours', fn (Builder $query) => $this->publishedTours($query))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug'])
                ->all(),
            'destinationTabs' => collect($featuredDestinations)->pluck('tab_key', 'tab_key')->map(fn ($key) => [
                'key' => $key,
                'label' => collect($featuredDestinations)->firstWhere('tab_key', $key)['tab_label'] ?? 'Điểm đến',
            ])->values()->all(),
            'customTourBackdropUrl' => $customTourBackdropUrl,
            'impactTitle' => $websiteSettings->impact_title,
            'impactStats' => $this->impactStats($websiteSettings),
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
                ])
                ->all(),
            'structuredData' => $structuredData->encode($structuredData->home($settings)),
            'stats' => [
                'destinations' => Destination::query()->where('is_active', true)->count(),
                'tours' => $this->publishedTours()->count(),
                'posts' => Post::query()->where('is_active', true)->count(),
            ],
        ]);
    }

    private function brandIntroduction(\App\Settings\WebsiteSettings $settings): array
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

    private function impactStats(\App\Settings\WebsiteSettings $settings): array
    {
        return [
            ['number' => $settings->impact_stat_one_number, 'label' => $settings->impact_stat_one_label],
            ['number' => $settings->impact_stat_two_number, 'label' => $settings->impact_stat_two_label],
            ['number' => $settings->impact_stat_three_number, 'label' => $settings->impact_stat_three_label],
            ['number' => $settings->impact_stat_four_number, 'label' => $settings->impact_stat_four_label],
        ];
    }

    private function customerGallery(): array
    {
        return [
            ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khách hàng tận hưởng biển xanh'],
            ['url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khách hàng khám phá núi rừng'],
            ['url' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khách hàng trải nghiệm phố cổ'],
            ['url' => 'https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khách hàng trên hành trình khám phá'],
            ['url' => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khách hàng tại điểm đến châu Âu'],
            ['url' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=900&q=85', 'alt' => 'Khoảnh khắc đáng nhớ của khách hàng'],
        ];
    }

    private function partners(\App\Settings\WebsiteSettings $settings): array
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
            ->where('is_featured', true)
            ->with(['destination', 'category', 'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->limit(3)
            ->get()
            ->map(fn (Tour $tour): array => $this->tourCard($tour))
            ->all();
    }

    private function promotionalTours(): array
    {
        return $this->withNextDeparture($this->publishedTours())
            ->whereHas('promotions', fn (Builder $query) => $this->activePromotion($query))
            ->with([
                'destination',
                'category',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
                'promotions' => fn ($query) => $this->activePromotion($query)->orderBy('discount_value'),
            ])
            ->orderBy('sort_order')
            ->limit(6)
            ->get()
            ->map(fn (Tour $tour): array => $this->tourCard($tour, $tour->promotions->first()))
            ->all();
    }

    private function destinations(): array
    {
        return Destination::query()
            ->where('is_active', true)
            ->with('parent')
            ->withCount(['tours' => fn ($query) => $query->where('is_active', true)])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
            ->map(function (Destination $destination): array {
                $tabLabel = $destination->parent?->name ?: 'Nổi bật';

                return [
                    'name' => $destination->name,
                    'slug' => $destination->slug,
                    'summary' => $destination->summary,
                    'image_url' => $this->imageUrl($destination->cover_image) ?: $this->mediaUrl($destination, 'cover'),
                    'tour_count' => $destination->tours_count,
                    'tab_key' => Str::slug($tabLabel),
                    'tab_label' => $tabLabel,
                ];
            })
            ->all();
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

    private function tourCard(Tour $tour, ?Promotion $promotion = null): array
    {
        $price = (float) $tour->starting_price;
        $salePrice = $this->salePrice($price, $promotion);
        $discountPercent = $this->discountPercent($price, $promotion);

        return [
            'id' => $tour->getKey(),
            'name' => $tour->name,
            'slug' => $tour->slug,
            'summary' => $tour->summary,
            'duration' => $tour->duration_days . ' ngày' . ($tour->duration_nights ? ' ' . $tour->duration_nights . ' đêm' : ''),
            'next_departure' => filled($tour->getAttribute('next_departure_date'))
                ? \Carbon\Carbon::parse($tour->getAttribute('next_departure_date'))->format('d/m/Y')
                : null,
            'price' => $price,
            'price_label' => $this->moneyLabel($price, $tour->currency),
            'sale_price_label' => $discountPercent ? $this->moneyLabel($salePrice, $tour->currency) : null,
            'discount_percent' => $discountPercent,
            'currency' => $tour->currency ?: 'VND',
            'destination' => $tour->destination?->name,
            'category' => $tour->category?->name,
            'image_url' => $this->imageUrl($tour->images->first()?->path)
                ?: $this->imageUrl($tour->destination?->cover_image)
                ?: $this->mediaUrl($tour->destination, 'cover'),
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

    private function withNextDeparture(Builder $query): Builder
    {
        return $query->withMin([
            'schedules as next_departure_date' => fn (Builder $scheduleQuery) => $scheduleQuery
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today()),
        ], 'departure_date');
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

        return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
    }

    private function linkUrl(?string $path): string
    {
        if (blank($path)) {
            return route('tours.index');
        }

        return Str::startsWith($path, ['http://', 'https://', '#', '/', 'mailto:', 'tel:']) ? $path : url($path);
    }
}
