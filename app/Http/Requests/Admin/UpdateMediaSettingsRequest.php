<?php

namespace App\Http\Requests\Admin;

use App\Services\MediaPolicy;
use App\Support\MediaFields;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_fill_keys(array_map(fn ($field) => $field.'_remove', MediaFields::SETTINGS), ['nullable', 'boolean']) + [
            'logo_url' => ['nullable', 'string', 'max:4096'],
            'favicon_master' => ['nullable', 'string', 'max:4096'],
            'image_share_url' => ['nullable', 'string', 'max:4096'],
            'page_banner_url' => ['nullable', 'string', 'max:4096'],
            'homepage_hero_url' => ['nullable', 'string', 'max:4096'],
            'about_image_url' => ['nullable', 'string', 'max:4096'],
            'media_allowed_extensions' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $allowed = [...MediaPolicy::IMAGES, ...MediaPolicy::DOCUMENTS];
                if (array_diff(array_map('trim', explode(',', strtolower($value))), $allowed)) {
                    $fail('Chỉ hỗ trợ JPG, JPEG, PNG, WEBP, GIF, PDF, DOC, DOCX.');
                }
            }],
            'media_max_size' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
