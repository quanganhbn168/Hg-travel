<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaOptimizationService
{
    public function optimize(Media $media): void
    {
        if (! in_array($media->mime_type, ['image/jpeg', 'image/png'], true)) {
            return;
        }
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();
        if (! $disk->exists($path)) {
            return;
        }
        $bytes = $disk->get($path);
        // APNG and images with non-default EXIF orientation retain originals until an orientation-aware pipeline is explicitly selected.
        if (str_contains($bytes, 'acTL')) {
            return;
        }
        if ($media->mime_type === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($media->getPath());
            if (isset($exif['Orientation']) && (int) $exif['Orientation'] !== 1) {
                return;
            }
        }
        $info = @getimagesizefromstring($bytes);
        if (! $info || config('media.max_pixels') < $info[0] * $info[1]) {
            return;
        }
        $source = @imagecreatefromstring($bytes);
        if (! $source) {
            throw new \RuntimeException('Cannot decode media #'.$media->id);
        }
        imagealphablending($source, false);
        imagesavealpha($source, true);
        $base = $media->id.'/derived/';
        try {
            ob_start();
            $ok = imagewebp($source, null, (int) config('media.webp_quality'));
            $webp = ob_get_clean();
            if (! $ok) {
                throw new \RuntimeException('WebP encoder failed.');
            }
            $converted = getimagesizefromstring($webp);
            if ($converted[0] !== $info[0] || $converted[1] !== $info[1]) {
                throw new \RuntimeException('Image dimensions changed.');
            }
            if (strlen($webp) < strlen($bytes)) {
                $disk->put($base.'display.webp', $webp);
                $media->setCustomProperty('webp_path', $base.'display.webp');
            }
            $width = min($info[0], (int) config('media.thumbnail_width'));
            $height = max(1, (int) round($info[1] * $width / $info[0]));
            $thumb = imagecreatetruecolor($width, $height);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
            ob_start();
            imagewebp($thumb, null, 85);
            $thumbnail = ob_get_clean();
            imagedestroy($thumb);
            $disk->put($base.'thumbnail.webp', $thumbnail);
            $media->setCustomProperty('thumbnail_path', $base.'thumbnail.webp');
            DB::transaction(function () use ($media): void {
                $current = Media::whereKey($media->id)->lockForUpdate()->first();
                if (! $current) {
                    return;
                }
                foreach (['webp_path', 'thumbnail_path'] as $key) {
                    if ($value = $media->getCustomProperty($key)) {
                        $current->setCustomProperty($key, $value);
                    }
                }
                $current->setCustomProperty('optimized_at', now()->toIso8601String())->save();
            });
        } finally {
            imagedestroy($source);
        }
    }
}
