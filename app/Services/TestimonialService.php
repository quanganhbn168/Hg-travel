<?php

namespace App\Services;

use App\Models\Testimonial;

final class TestimonialService
{
    public function save(array $data, ?Testimonial $testimonial = null): Testimonial
    {
        $testimonial ??= new Testimonial;
        $data['avatar_path'] = app(MediaReferenceService::class)->field($data, 'avatar_path', $testimonial->avatar_path);
        unset($data['avatar_path_remove']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $testimonial->fill($data)->save();

        return $testimonial;
    }
}
