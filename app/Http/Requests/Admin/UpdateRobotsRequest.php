<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRobotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermissionTo('settings.update', 'web') ?? false;
    }

    public function rules(): array
    {
        return [
            'robots_revision' => ['required', 'string', 'regex:/\A(?:missing|[a-f0-9]{64})\z/'],
            'robots_content' => ['bail', 'required', 'string', 'max:50000', function (string $attribute, mixed $value, Closure $fail): void {
                if (preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F]/', $value)) {
                    $fail('robots.txt chỉ nhận văn bản thuần, không nhận HTML, PHP hoặc ký tự điều khiển.');

                    return;
                }

                $hasUserAgent = false;
                foreach (preg_split('/\r\n|\r|\n/', $value) as $index => $line) {
                    $line = trim(explode('#', $line, 2)[0]);
                    if ($line === '') {
                        continue;
                    }
                    if (! preg_match('/^(User-agent|Allow|Disallow|Sitemap|Crawl-delay)\s*:\s*(.*)$/i', $line, $match)) {
                        $fail('Dòng '.($index + 1).' không phải quy tắc robots.txt hợp lệ. Dùng User-agent, Allow, Disallow, Sitemap hoặc Crawl-delay.');

                        return;
                    }
                    $directive = strtolower($match[1]);
                    $argument = trim($match[2]);
                    if ($directive === 'user-agent') {
                        $hasUserAgent = $hasUserAgent || $argument !== '';
                        $valid = $argument !== '';
                    } elseif ($directive === 'sitemap') {
                        $valid = filter_var($argument, FILTER_VALIDATE_URL) && in_array(parse_url($argument, PHP_URL_SCHEME), ['http', 'https'], true);
                    } elseif ($directive === 'crawl-delay') {
                        $valid = $hasUserAgent && is_numeric($argument) && (float) $argument >= 0;
                    } else {
                        $valid = $hasUserAgent && ($argument === '' || str_starts_with($argument, '/'));
                    }
                    if (! $valid) {
                        $fail('Giá trị tại dòng '.($index + 1).' không hợp lệ. Đặt User-agent trước Allow/Disallow, đường dẫn bắt đầu bằng / và Sitemap dùng URL http(s) đầy đủ.');

                        return;
                    }
                }
                if (! $hasUserAgent) {
                    $fail('robots.txt cần ít nhất một dòng User-agent, ví dụ User-agent: *.');
                }
            }],
        ];
    }

    public function attributes(): array
    {
        return ['robots_content' => 'nội dung robots.txt', 'robots_revision' => 'phiên bản robots.txt'];
    }
}
