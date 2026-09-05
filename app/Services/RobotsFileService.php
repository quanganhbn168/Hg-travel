<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Throwable;

class RobotsFileService
{
    public function defaults(): string
    {
        $sitemapUrl = rtrim(config('app.url'), '/').'/sitemap.xml';

        return "User-agent: *\nAllow: /\nDisallow: /admin$\nDisallow: /admin?\nDisallow: /admin/\n\nSitemap: {$sitemapUrl}\n";
    }

    /** @return array{content: string, revision: string, exists: bool} */
    public function read(): array
    {
        $path = public_path('robots.txt');
        clearstatcache(true, $path);

        if (! File::exists($path)) {
            return ['content' => $this->defaults(), 'revision' => 'missing', 'exists' => false];
        }

        $content = File::get($path);

        return ['content' => $content, 'revision' => hash('sha256', $content), 'exists' => true];
    }

    public function save(string $content, string $revision): void
    {
        $path = public_path('robots.txt');

        try {
            Cache::lock('seo:robots-file:'.sha1($path), 10)->block(5, function () use ($path, $content, $revision): void {
                $current = $this->read();
                if (! hash_equals($current['revision'], $revision)) {
                    throw ValidationException::withMessages([
                        'robots_content' => 'robots.txt đã được thay đổi sau khi mở form. Hãy sao chép nội dung đang sửa, tải lại trang rồi đối chiếu trước khi lưu.',
                    ]);
                }

                // Normalize editor line endings; the only writable target is this fixed public file.
                $normalized = rtrim(str_replace(["\r\n", "\r"], "\n", $content))."\n";
                try {
                    File::replace($path, $normalized, 0644);
                } catch (Throwable $exception) {
                    report($exception);
                    throw ValidationException::withMessages([
                        'robots_content' => 'Không ghi được public/robots.txt. Hãy kiểm tra quyền ghi thư mục public và thử lưu lại; chưa ghi nhận lưu thành công.',
                    ]);
                }
            });
        } catch (LockTimeoutException $exception) {
            throw ValidationException::withMessages([
                'robots_content' => 'robots.txt đang được lưu bởi một thao tác khác. Vui lòng thử lại sau vài giây.',
            ]);
        }
    }
}
