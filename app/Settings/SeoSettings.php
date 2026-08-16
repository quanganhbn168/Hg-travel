<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public ?string $seo_title;
    public ?string $seo_description;
    public ?string $seo_keywords;

    public static function group(): string
    {
        return 'seo';
    }
}
