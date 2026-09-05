<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MediaSettings extends Settings
{
    public string $media_allowed_extensions;

    public int $media_max_size;

    public ?string $logo_url;

    public ?string $favicon_url;

    public ?string $image_share_url;

    public ?string $page_banner_url;

    public ?string $homepage_hero_url;

    public ?string $about_image_url;

    public array $media_ids = [];

    public static function group(): string
    {
        return 'media';
    }
}
