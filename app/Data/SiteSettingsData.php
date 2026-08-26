<?php

namespace App\Data;

final class SiteSettingsData
{
    public function __construct(
        public readonly string $site_name,
        public readonly ?string $company_name,
        public readonly ?string $legal_representative,
        public readonly ?string $tax_code,
        public readonly ?string $travel_license_number,
        public readonly ?string $brand_statement,
        public readonly ?string $contact_email,
        public readonly ?string $contact_email_secondary,
        public readonly ?string $contact_email_tertiary,
        public readonly ?string $contact_phone,
        public readonly ?string $contact_phone_secondary,
        public readonly ?string $office_address,
        public readonly ?string $seo_title,
        public readonly ?string $seo_description,
        public readonly ?string $seo_keywords,
        public readonly ?string $logo_url,
        public readonly ?string $favicon_url,
        public readonly ?string $image_share_url,
        public readonly ?string $page_banner_url,
        public readonly ?string $facebook_url,
        public readonly ?string $instagram_url,
        public readonly ?string $youtube_url,
        public readonly ?string $zalo_url,
        public readonly ?string $messenger_url,
        public readonly ?string $whatsapp_url,
    ) {}
}
