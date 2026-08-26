<?php

namespace App\Services;

use App\Settings\BusinessSettings;
use App\Settings\ContactSettings;
use App\Settings\MediaSettings;
use App\Settings\SeoSettings;
use App\Settings\TourSettings;
use App\Settings\WebsiteSettings;
use Spatie\LaravelSettings\Settings;

class SettingService
{
    public function __construct(private readonly SiteSettingsService $siteSettings) {}

    public function website(): WebsiteSettings
    {
        return $this->siteSettings->website();
    }

    public function business(): BusinessSettings
    {
        return $this->siteSettings->business();
    }

    public function media(): MediaSettings
    {
        return $this->siteSettings->media();
    }

    public function seo(): SeoSettings
    {
        return $this->siteSettings->seo();
    }

    public function contact(): ContactSettings
    {
        return $this->siteSettings->contact();
    }

    public function tour(): TourSettings
    {
        return $this->siteSettings->tour();
    }

    /** @param array<string, mixed> $data */
    public function updateWebsite(array $data): void
    {
        $this->save($this->website(), $data, [
            'site_name', 'about_title', 'about_paragraph_one', 'about_paragraph_two',
            'custom_tour_title', 'custom_tour_description', 'impact_title',
            'impact_stat_one_number', 'impact_stat_one_label',
            'impact_stat_three_number', 'impact_stat_three_label', 'impact_stat_four_number', 'impact_stat_four_label', 'partner_names',
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateBusiness(array $data): void
    {
        $this->save($this->business(), $data, [
            'company_name', 'legal_representative', 'tax_code', 'travel_license_number', 'brand_statement',
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateMedia(array $data): void
    {
        $this->save($this->media(), $data, [
            'logo_url', 'favicon_url', 'image_share_url', 'page_banner_url', 'homepage_hero_url', 'about_image_url',
            'media_allowed_extensions', 'media_max_size',
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateSeo(array $data): void
    {
        $this->save($this->seo(), $data, ['seo_title', 'seo_description', 'seo_keywords']);
    }

    /** @param array<string, mixed> $data */
    public function updateContact(array $data): void
    {
        $this->save($this->contact(), $data, [
            'contact_email', 'contact_email_secondary', 'contact_email_tertiary',
            'contact_phone', 'contact_phone_secondary', 'office_address',
            'facebook_url', 'instagram_url', 'youtube_url', 'zalo_url', 'messenger_url', 'whatsapp_url',
        ]);
    }

    /** @param array<string, mixed> $data */
    public function updateTour(array $data): void
    {
        $this->save($this->tour(), $data, ['show_schedules', 'show_seat_availability', 'schedule_note']);
    }

    /** @param array<string, mixed> $data @param list<string> $fields */
    private function save(Settings $settings, array $data, array $fields): void
    {
        $settings->fill(collect($data)->only($fields)->all())->save();
    }
}
