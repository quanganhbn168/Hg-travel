<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaReferenceService
{
    private ?array $paths = null;

    private array $ids = [];

    public function forget(): void
    {
        $this->paths = null;
        $this->ids = [];
    }

    public function localPath(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }
        $value = trim($value);
        $host = parse_url($value, PHP_URL_HOST);
        $hosts = [...config('media.legacy_hosts', []), parse_url(config('app.url'), PHP_URL_HOST)];
        if ($host && ! in_array(strtolower($host), array_map('strtolower', array_filter($hosts)), true)) {
            return null;
        }
        if (! $host && preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)) {
            return null;
        }
        $path = ltrim(rawurldecode((string) parse_url($value, PHP_URL_PATH)), '/');
        if (str_contains($path, '\\') || str_contains($path, "\0") || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }

    public function originalPath(Media $media): string
    {
        return 'media/'.ltrim($media->getPathRelativeToRoot(), '/');
    }

    public function find(?string $value): ?Media
    {
        $isId = preg_match('/^media:(\d+)$/', (string) $value, $matches);
        $path = $this->localPath($value);
        if (! $isId && (! $path || ! str_starts_with($path, 'media/'))) {
            return null;
        }
        if ($this->paths === null) {
            $this->paths = [];
            foreach (Media::all() as $media) {
                $this->ids[$media->id] = $media;
                $this->paths[$this->originalPath($media)] = $media;
                foreach ((array) $media->getCustomProperty('aliases', []) as $alias) {
                    $this->paths[$alias] = $media;
                }
            }
        }

        return $isId ? ($this->ids[(int) $matches[1]] ?? null) : ($this->paths[$path] ?? null);
    }

    /** Resolve input to a portable original path; only owned pending uploads can be claimed. */
    public function resolve(?string $value, string $field = 'image', bool $imagesOnly = true): array
    {
        if (blank($value)) {
            return ['path' => null, 'id' => null];
        }
        $media = $this->find($value);
        if ($media) {
            // Re-read state when attaching, since another request can archive/reserve the same file.
            $media->refresh();
            $state = $media->getCustomProperty('state', 'ready');
            if ($state === 'archived' || ($state === 'pending' && (int) $media->getCustomProperty('uploaded_by') !== (int) auth('admin')->id())) {
                throw ValidationException::withMessages([$field => 'Ảnh không khả dụng hoặc không thuộc lần tải lên của bạn.']);
            }
            if ($imagesOnly && ! str_starts_with($media->mime_type, 'image/')) {
                throw ValidationException::withMessages([$field => 'Vị trí này chỉ nhận hình ảnh.']);
            }
            if (! Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
                throw ValidationException::withMessages([$field => 'File ảnh không còn trên máy chủ. Hãy tải lại ảnh.']);
            }
            // Reserve before the parent save. Failed parent validation never causes a used file to be deleted.
            if ($state === 'pending') {
                Media::whereKey($media->id)->where('custom_properties->state', 'pending')->update(['custom_properties->state' => 'ready']);
            }

            return ['path' => $this->originalPath($media), 'id' => $media->id];
        }
        if (str_starts_with((string) $value, 'media:')) {
            throw ValidationException::withMessages([$field => 'Ảnh đã hết hạn hoặc không còn tồn tại.']);
        }
        $path = $this->localPath($value);
        if ($path !== null) {
            return ['path' => $path, 'id' => null];
        }
        if (! filter_var($value, FILTER_VALIDATE_URL) || ! in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw ValidationException::withMessages([$field => 'Đường dẫn ảnh không hợp lệ.']);
        }

        return ['path' => $value, 'id' => null];
    }

    public function url(?string $value, ?string $fallback = null, bool $thumbnail = false): ?string
    {
        if (blank($value)) {
            return $fallback ? asset($fallback) : null;
        }
        $media = $this->find($value);
        if ($media) {
            $original = $media->getPathRelativeToRoot();
            if (! Storage::disk($media->disk)->exists($original)) {
                return $fallback ? asset($fallback) : null;
            }
            $derived = $media->getCustomProperty($thumbnail ? 'thumbnail_path' : 'webp_path');
            $path = $derived && Storage::disk($media->disk)->exists($derived) ? $derived : $original;

            return rtrim(config('app.url'), '/').'/media/'.$path;
        }
        $path = $this->localPath($value);
        if ($path !== null) {
            $exists = str_starts_with($path, 'media/')
                ? Storage::disk('public_media')->exists(substr($path, 6))
                : is_file(public_path($path));

            return $exists ? asset($path) : ($fallback ? asset($fallback) : null);
        }

        return filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true) ? $value : ($fallback ? asset($fallback) : null);
    }

    public function content(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->content($item), $value);
        }
        if (! is_string($value)) {
            return $value;
        }
        if ($this->find($value)) {
            return '/'.$this->resolve($value, 'content', false)['path'];
        }

        return preg_replace_callback('/((?:src|href|data-mce-src)\s*=\s*)([\x22\x27])(.*?)\2/iu', function ($match) {
            $source = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (! $this->find($source)) {
                return $match[0];
            }

            return $match[1].$match[2].'/'.$this->resolve($source, 'content', false)['path'].$match[2];
        }, $value);
    }

    public function field(array $data, string $field, ?string $current = null): ?string
    {
        if ((bool) ($data[$field.'_remove'] ?? false)) {
            return null;
        }

        return filled($data[$field] ?? null) ? $data[$field] : $current;
    }
}
