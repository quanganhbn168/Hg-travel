<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use App\Settings\MediaSettings;
use Spatie\LaravelSettings\Settings;
use Throwable;

class SiteSettingsService
{
    public function general(): GeneralSettings
    {
        return $this->load(GeneralSettings::class, [
            'site_name' => config('app.name', 'Du lịch'),
            'company_name' => null,
            'contact_email' => null,
            'contact_email_secondary' => null,
            'contact_email_tertiary' => null,
            'contact_phone' => null,
            'contact_phone_secondary' => null,
            'office_address' => null,
            'seo_title' => config('app.name', 'Du lịch'),
            'seo_description' => null,
            'seo_keywords' => null,
            'logo_url' => null,
            'favicon_url' => null,
            'image_share_url' => null,
            'page_banner_url' => null,
            'facebook_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'zalo_url' => null,
            'messenger_url' => null,
            'whatsapp_url' => null,
        ]);
    }

    public function media(): MediaSettings
    {
        return $this->load(MediaSettings::class, [
            'media_allowed_extensions' => 'jpg,jpeg,png,webp,gif,pdf,doc,docx',
            'media_max_size' => 10,
        ]);
    }

    /**
     * @template T of Settings
     * @param class-string<T> $settingsClass
     * @param array<string, mixed> $fallback
     * @return T
     */
    private function load(string $settingsClass, array $fallback): Settings
    {
        try {
            $settings = app($settingsClass);
            $settings->toArray();

            return $settings;
        } catch (Throwable) {
            return $settingsClass::fake($fallback, false);
        }
    }
}
