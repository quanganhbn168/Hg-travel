<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class FaviconService
{
    private const MASTER_SIZE = 512;

    /** @var list<int> */
    private const PNG_SIZES = [16, 32, 180, 192, 512];

    /** @return array{master: string, files: list<string>} */
    public function generateFromUpload(string $url): array
    {
        return $this->generateFromPath($this->uploadedMediaPath($url));
    }

    /** @return array{master: string, files: list<string>} */
    public function generateFromPath(string $sourcePath): array
    {
        if (! extension_loaded('gd')) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Máy chủ chưa bật PHP GD để tạo bộ favicon.',
            ]);
        }

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Không tìm thấy file master favicon.',
            ]);
        }

        $imageInfo = @getimagesize($sourcePath);

        if ($imageInfo === false || ! in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Favicon master phải là ảnh PNG, JPG, WEBP hoặc GIF hợp lệ.',
            ]);
        }

        if (min((int) $imageInfo[0], (int) $imageInfo[1]) < self::MASTER_SIZE) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Favicon master cần tối thiểu 512 × 512 px để các kích thước nhỏ vẫn sắc nét.',
            ]);
        }

        $binary = file_get_contents($sourcePath);
        $source = $binary === false ? false : @imagecreatefromstring($binary);

        if ($source === false) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Không thể đọc ảnh master favicon.',
            ]);
        }

        $master = $this->square($source, self::MASTER_SIZE);
        $masterPng = $this->png($master);
        imagedestroy($master);

        $files = [];
        $this->write('favicon-master.png', $masterPng);
        $files[] = 'favicon-master.png';

        $pngs = [];

        foreach (self::PNG_SIZES as $size) {
            $icon = $this->square($source, $size);
            $pngs[$size] = $this->png($icon);
            imagedestroy($icon);
        }

        imagedestroy($source);

        $this->write('favicon-16x16.png', $pngs[16]);
        $this->write('favicon-32x32.png', $pngs[32]);
        $this->write('apple-touch-icon.png', $pngs[180]);
        $this->write('android-chrome-192x192.png', $pngs[192]);
        $this->write('android-chrome-512x512.png', $pngs[512]);
        $this->write('favicon.svg', $this->svg($masterPng));
        $this->write('favicon.ico', $this->ico([
            16 => $pngs[16],
            32 => $pngs[32],
            48 => $this->pngFromMaster($masterPng, 48),
        ]));
        $this->write('site.webmanifest', $this->manifest());

        return [
            'master' => public_path('favicon-master.png'),
            'files' => [
                ...$files,
                'favicon.svg',
                'favicon.ico',
                'favicon-16x16.png',
                'favicon-32x32.png',
                'apple-touch-icon.png',
                'android-chrome-192x192.png',
                'android-chrome-512x512.png',
                'site.webmanifest',
            ],
        ];
    }

    private function uploadedMediaPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $relative = ltrim(urldecode(is_string($path) ? $path : $url), '/');

        if (! Str::startsWith($relative, 'media/')) {
            throw ValidationException::withMessages([
                'favicon_master' => 'Hãy dùng ảnh vừa tải lên từ Thư viện media làm favicon master.',
            ]);
        }

        $mediaRoot = realpath(Storage::disk('public_media')->path(''));
        $source = $mediaRoot ? realpath($mediaRoot.DIRECTORY_SEPARATOR.substr($relative, 6)) : false;

        if (! $mediaRoot || ! $source || ! Str::startsWith(strtolower($source), strtolower($mediaRoot.DIRECTORY_SEPARATOR))) {
            throw ValidationException::withMessages([
                'favicon_master' => 'File favicon master không thuộc Thư viện media của website.',
            ]);
        }

        return $source;
    }

    private function square(\GdImage $source, int $size): \GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = max($size / $sourceWidth, $size / $sourceHeight);
        $targetWidth = (int) round($sourceWidth * $scale);
        $targetHeight = (int) round($sourceHeight * $scale);

        imagecopyresampled(
            $canvas,
            $source,
            (int) floor(($size - $targetWidth) / 2),
            (int) floor(($size - $targetHeight) / 2),
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        return $canvas;
    }

    private function png(\GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 6);

        return (string) ob_get_clean();
    }

    private function pngFromMaster(string $master, int $size): string
    {
        $image = imagecreatefromstring($master);
        $icon = $this->square($image, $size);
        $png = $this->png($icon);
        imagedestroy($icon);
        imagedestroy($image);

        return $png;
    }

    /** @param array<int, string> $pngs */
    private function ico(array $pngs): string
    {
        $header = pack('vvv', 0, 1, count($pngs));
        $directory = '';
        $payload = '';
        $offset = 6 + (16 * count($pngs));

        foreach ($pngs as $size => $png) {
            $directory .= pack(
                'CCCCvvVV',
                $size === 256 ? 0 : $size,
                $size === 256 ? 0 : $size,
                0,
                0,
                1,
                32,
                strlen($png),
                $offset,
            );
            $payload .= $png;
            $offset += strlen($png);
        }

        return $header.$directory.$payload;
    }

    private function svg(string $masterPng): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
            .'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">'
            .'<image width="512" height="512" href="data:image/png;base64,'.base64_encode($masterPng).'"/>'
            .'</svg>'.PHP_EOL;
    }

    private function manifest(): string
    {
        return json_encode([
            'name' => 'HG Trip',
            'short_name' => 'HG Trip',
            'icons' => [
                ['src' => '/android-chrome-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/android-chrome-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
            'theme_color' => '#0b5a70',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    }

    private function write(string $filename, string $contents): void
    {
        File::put(public_path($filename), $contents, true);
    }
}
