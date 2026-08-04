<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public ?string $company_name;
    public ?string $contact_email;
    public ?string $contact_email_secondary;
    public ?string $contact_email_tertiary;
    public ?string $contact_phone;
    public ?string $contact_phone_secondary;
    public ?string $office_address;
    public ?string $seo_title;
    public ?string $seo_description;
    public ?string $seo_keywords;
    public ?string $logo_url;
    public ?string $favicon_url;
    public ?string $image_share_url;
    public ?string $page_banner_url;
    public ?string $facebook_url;
    public ?string $instagram_url;
    public ?string $youtube_url;
    public ?string $zalo_url;
    public ?string $messenger_url;
    public ?string $whatsapp_url;

    public static function group(): string
    {
        return 'general';
    }
}
