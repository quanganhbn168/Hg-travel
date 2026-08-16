<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public ?string $contact_email;
    public ?string $contact_email_secondary;
    public ?string $contact_email_tertiary;
    public ?string $contact_phone;
    public ?string $contact_phone_secondary;
    public ?string $office_address;
    public ?string $facebook_url;
    public ?string $instagram_url;
    public ?string $youtube_url;
    public ?string $zalo_url;
    public ?string $messenger_url;
    public ?string $whatsapp_url;

    public static function group(): string
    {
        return 'contact';
    }
}
