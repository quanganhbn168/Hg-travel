<?php

namespace App\View\Composers;

use App\Models\Page;
use App\Services\FrontendMenuService;
use App\Services\SiteSettingsService;
use App\Services\TravelServiceCatalog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterViewComposer
{
    public function __construct(
        private readonly SiteSettingsService $siteSettings,
        private readonly FrontendMenuService $menus,
        private readonly TravelServiceCatalog $serviceCatalog,
    ) {}

    public function compose(View $view): void
    {
        $settings = $this->siteSettings->general();
        $phones = $this->contactPhones($settings);
        $emails = $this->contactEmails($settings);
        $logo = $this->assetUrl($settings->logo_url) ?: asset('images/logo-hg.png');

        $view->with([
            'siteSettings' => $settings,
            'brandName' => $settings->site_name ?: config('app.name', env('APP_NAME', 'APP_NAME')),
            'siteAssets' => [
                'logo' => $logo,
                'favicon' => $this->assetUrl($settings->favicon_url) ?: $logo,
                'share' => $this->assetUrl($settings->image_share_url) ?: asset('images/image_share.png'),
                'page_banner' => $this->assetUrl($settings->page_banner_url) ?: asset('images/page-banner-coast-v1.png'),
            ],
            'siteLinks' => [
                'phone' => $phones[0]['value'] ?? null,
                'phone_href' => $phones[0]['href'] ?? null,
                'phones' => $phones,
                'email' => $emails[0] ?? null,
                'emails' => $emails,
                'facebook' => $settings->facebook_url,
                'instagram' => $settings->instagram_url,
                'youtube' => $settings->youtube_url,
                'zalo' => $settings->zalo_url,
                'messenger' => $settings->messenger_url,
                'whatsapp' => $settings->whatsapp_url,
            ],
            'headerMenuItems' => $this->activeItems('header'),
            'footerMenuItems' => $this->activeItems('footer'),
            'footerServices' => array_slice($this->serviceCatalog->all(), 0, 3),
            'footerPages' => Page::query()
                ->where('is_active', true)
                ->whereIn('slug', ['chinh-sach-bao-mat', 'dieu-khoan'])
                ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->orderBy('sort_order')
                ->get(['name', 'slug']),
        ]);
    }

    private function activeItems(string $location): array
    {
        return array_map(fn (array $item): array => $this->markActive($item), $this->menus->items($location));
    }

    private function markActive(array $item): array
    {
        $item['children'] = array_map(fn (array $child): array => $this->markActive($child), $item['children']);
        $item['active'] = $this->isActive($item) || collect($item['children'])->contains('active', true);

        return $item;
    }

    private function isActive(array $item): bool
    {
        if ($item['route_name'] && Route::has($item['route_name'])) {
            return request()->routeIs($item['route_name'] . '*');
        }

        $url = $item['url'] ?: '/';

        if (filled(parse_url($url, PHP_URL_FRAGMENT))) {
            return false;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = (string) parse_url($url, PHP_URL_QUERY);

        if ($query !== '') {
            parse_str($query, $expectedQuery);

            foreach ($expectedQuery as $key => $value) {
                if ((string) request()->query($key, '') !== (string) $value) {
                    return false;
                }
            }

            return request()->path() === $path;
        }

        return $path === '' ? request()->is('/') : request()->is($path) || request()->is($path . '/*');
    }

    private function assetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
    }

    private function phoneHref(?string $phone): ?string
    {
        return filled($phone) ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : null;
    }

    private function contactPhones(object $settings): array
    {
        return collect([
            ['label' => 'Ms Giang', 'value' => $settings->contact_phone],
            ['label' => 'Ms Hương', 'value' => $settings->contact_phone_secondary],
        ])->filter(fn (array $phone): bool => filled($phone['value']))
            ->map(fn (array $phone): array => $phone + ['href' => $this->phoneHref($phone['value'])])
            ->values()
            ->all();
    }

    private function contactEmails(object $settings): array
    {
        return collect([
            $settings->contact_email,
            $settings->contact_email_secondary,
            $settings->contact_email_tertiary,
        ])->filter(fn (?string $email): bool => filled($email))->values()->all();
    }
}
