<?php

namespace App\Services;

class SettingService
{
    public function __construct(private readonly SiteSettingsService $siteSettings) {}

    public function general(): array
    {
        return array_merge(
            $this->siteSettings->general()->toArray(),
            $this->siteSettings->media()->toArray(),
        );
    }

    public function updateGeneral(array $data): void
    {
        $general = $this->siteSettings->general();
        $general->fill(collect($data)->only([
            'site_name', 'company_name', 'contact_email', 'contact_email_secondary', 'contact_email_tertiary',
            'contact_phone', 'contact_phone_secondary', 'office_address',
            'seo_title', 'seo_description', 'seo_keywords', 'logo_url', 'favicon_url', 'page_banner_url',
            'facebook_url', 'instagram_url', 'youtube_url', 'zalo_url', 'messenger_url', 'whatsapp_url',
        ])->all())->save();

        $media = $this->siteSettings->media();
        $media->fill(collect($data)->only([
            'media_allowed_extensions', 'media_max_size',
        ])->all())->save();
    }
}
