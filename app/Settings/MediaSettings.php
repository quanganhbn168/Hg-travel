<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MediaSettings extends Settings
{
    public string $media_allowed_extensions;
    public int $media_max_size;

    public static function group(): string
    {
        return 'media';
    }
}
