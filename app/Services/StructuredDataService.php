<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StructuredDataService
{
    public function encode(array $schema): string
    {
        return json_encode(
            $schema,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT,
        ) ?: '{}';
    }

    public function home(GeneralSettings $settings): array
    {
        $siteUrl = rtrim(url('/'), '/');
        $siteName = $settings->company_name ?: $settings->site_name ?: config('app.name', 'Du lịch');
        $socialLinks = collect([
            $settings->facebook_url,
            $settings->instagram_url,
            $settings->youtube_url,
            $settings->zalo_url,
            $settings->messenger_url,
            $settings->whatsapp_url,
        ])->filter(fn (?string $url): bool => filter_var($url, FILTER_VALIDATE_URL) !== false)->values()->all();

        $agency = array_filter([
            '@type' => 'TravelAgency',
            '@id' => $siteUrl.'#organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'telephone' => $settings->contact_phone,
            'email' => $settings->contact_email,
            'address' => $settings->office_address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->office_address,
                'addressCountry' => 'VN',
            ] : null,
            'sameAs' => $socialLinks ?: null,
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $agency,
                [
                    '@type' => 'WebSite',
                    '@id' => $siteUrl.'#website',
                    'url' => $siteUrl,
                    'name' => $siteName,
                    'inLanguage' => str_replace('_', '-', app()->getLocale()),
                    'publisher' => ['@id' => $siteUrl.'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => $siteUrl.'/tours?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => $siteUrl.'/#webpage',
                    'url' => $siteUrl.'/',
                    'name' => $settings->seo_title ?: $siteName,
                    'description' => $settings->seo_description,
                    'isPartOf' => ['@id' => $siteUrl.'#website'],
                    'about' => ['@id' => $siteUrl.'#organization'],
                ],
            ],
        ];
    }

    public function tourListing(array $page, LengthAwarePaginator $tours, ?array $activeCategory = null): array
    {
        $canonical = url()->current();
        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Tour du lịch', 'url' => url('/tours')],
        ];

        if ($activeCategory) {
            $breadcrumbItems[] = [
                'name' => $activeCategory['name'],
                'url' => url('/tours/danh-muc/'.$activeCategory['slug']),
            ];
        }

        $itemListId = $canonical.'#tour-list';

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'CollectionPage',
                    '@id' => $canonical.'#webpage',
                    'name' => $page['title'],
                    'description' => $page['description'],
                    'url' => $canonical,
                    'isPartOf' => ['@id' => rtrim(url('/'), '/').'#website'],
                    'breadcrumb' => ['@id' => $canonical.'#breadcrumb'],
                    'mainEntity' => ['@id' => $itemListId],
                ],
                $this->breadcrumb($breadcrumbItems, $canonical.'#breadcrumb'),
                [
                    '@type' => 'ItemList',
                    '@id' => $itemListId,
                    'name' => $page['title'],
                    'numberOfItems' => $tours->total(),
                    'itemListElement' => collect($tours->items())->values()->map(fn (array $tour, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => ($tours->firstItem() ?: 1) + $index,
                        'name' => $tour['name'],
                        'url' => url('/tours/'.$tour['slug']),
                    ])->all(),
                ],
            ],
        ];
    }

    public function tourDetail(array $tour, string $siteName): array
    {
        $canonical = url('/tours/'.$tour['slug']);
        $breadcrumbItems = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Tour du lịch', 'url' => url('/tours')],
        ];

        if ($tour['category'] && $tour['category_slug']) {
            $breadcrumbItems[] = [
                'name' => $tour['category'],
                'url' => url('/tours/danh-muc/'.$tour['category_slug']),
            ];
        }

        $trip = array_filter([
            '@type' => 'TouristTrip',
            '@id' => $canonical.'#tour',
            'name' => $tour['name'],
            'description' => Str::limit(strip_tags((string) $tour['description']), 300, '...'),
            'url' => $canonical,
            'image' => collect($tour['gallery'])->pluck('url')->values()->all(),
            'touristDestination' => $tour['destination'] ? [
                '@type' => 'Place',
                'name' => $tour['destination'],
            ] : null,
            'provider' => [
                '@type' => 'TravelAgency',
                'name' => $siteName,
                'url' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => $tour['currency'] ?: 'VND',
                'price' => $tour['price'],
                'availability' => $tour['booking_open'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
            'itinerary' => $this->itinerary($tour['itineraries']),
            'aggregateRating' => $tour['review_count'] > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $tour['average_rating'],
                'bestRating' => 5,
                'worstRating' => 1,
                'ratingCount' => $tour['review_count'],
            ] : null,
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebPage',
                    '@id' => $canonical.'#webpage',
                    'name' => $tour['seo_title'] ?: $tour['name'],
                    'description' => $tour['seo_description'] ?: $tour['summary'],
                    'url' => $canonical,
                    'mainEntity' => ['@id' => $canonical.'#tour'],
                    'breadcrumb' => ['@id' => $canonical.'#breadcrumb'],
                ],
                $this->breadcrumb($breadcrumbItems + [['name' => $tour['name'], 'url' => $canonical]], $canonical.'#breadcrumb'),
                $trip,
            ],
        ];
    }

    private function breadcrumb(array $items, string $id): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $id,
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ])->all(),
        ];
    }

    private function itinerary(array $items): ?array
    {
        if ($items === []) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => 'Ngày '.$item['day_number'].' · '.$item['title'],
                'description' => $item['description'],
            ], fn (mixed $value): bool => $value !== null && $value !== ''),)->all(),
        ];
    }
}
