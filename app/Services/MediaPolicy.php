<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

final class MediaPolicy
{
    public const IMAGES = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public const DOCUMENTS = ['pdf', 'doc', 'docx'];

    public function extensions(bool $imagesOnly = true): array
    {
        $configured = explode(',', strtolower(app(SiteSettingsService::class)->media()->media_allowed_extensions));

        return array_values(array_intersect(array_map('trim', $configured), $imagesOnly ? self::IMAGES : [...self::IMAGES, ...self::DOCUMENTS]));
    }

    public function maxMegabytes(): int
    {
        return max(1, min(100, app(SiteSettingsService::class)->media()->media_max_size));
    }

    public function rules(bool $imagesOnly = true): array
    {
        return ['required', 'file', 'max:'.($this->maxMegabytes() * 1024), 'mimes:'.implode(',', $this->extensions($imagesOnly) ?: ['disabled']), ...($imagesOnly ? ['image'] : [])];
    }

    public function inspect(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return [];
        }

        $info = @getimagesize($file->getPathname());
        if (! $info || config('media.max_pixels') < $info[0] * $info[1]) {
            throw ValidationException::withMessages(['file' => 'Ảnh không đọc được hoặc vượt giới hạn 40 triệu pixel.']);
        }

        return ['width' => $info[0], 'height' => $info[1]];
    }
}
