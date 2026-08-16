<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'logo_url' => ['nullable', 'string', 'max:4096'],
            'favicon_url' => ['nullable', 'string', 'max:4096'],
            'image_share_url' => ['nullable', 'string', 'max:4096'],
            'page_banner_url' => ['nullable', 'string', 'max:4096'],
            'homepage_hero_url' => ['nullable', 'string', 'max:4096'],
            'about_image_url' => ['nullable', 'string', 'max:4096'],
            'media_allowed_extensions' => ['required', 'string', 'max:255'],
            'media_max_size' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
