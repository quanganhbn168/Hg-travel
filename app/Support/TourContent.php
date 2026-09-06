<?php

namespace App\Support;

class TourContent
{
    public static function sectionTypes(): array
    {
        return [
            'highlights' => 'Điểm nổi bật',
            'included' => 'Giá tour bao gồm',
            'excluded' => 'Giá tour không bao gồm',
            'visa' => 'Hồ sơ visa',
            'conditions' => 'Điều kiện đăng ký',
            'policy' => 'Chính sách',
            'notes' => 'Lưu ý',
            'pricing' => 'Thông tin giá bổ sung',
            'other' => 'Thông tin khác',
        ];
    }

    public static function lines(?string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\s*\|\s*|\R/u', (string) $value) ?: []), fn ($line) => $line !== ''));
    }

    public static function html(?string $value): string
    {
        if (! filled($value)) {
            return '';
        }

        return preg_match('/<\/?[a-z][^>]*>/i', $value) ? $value : '<p>'.nl2br(e($value)).'</p>';
    }
}
