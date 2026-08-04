<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_email_secondary' => ['nullable', 'email', 'max:255'],
            'contact_email_tertiary' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_phone_secondary' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'seo_keywords' => ['nullable', 'string', 'max:1000'],
            'logo_url' => ['nullable', 'string', 'max:4096'],
            'favicon_url' => ['nullable', 'string', 'max:4096'],
            'image_share_url' => ['nullable', 'string', 'max:4096'],
            'page_banner_url' => ['nullable', 'string', 'max:4096'],
            'facebook_url' => ['nullable', 'url', 'max:4096'],
            'instagram_url' => ['nullable', 'url', 'max:4096'],
            'youtube_url' => ['nullable', 'url', 'max:4096'],
            'zalo_url' => ['nullable', 'url', 'max:4096'],
            'messenger_url' => ['nullable', 'url', 'max:4096'],
            'whatsapp_url' => ['nullable', 'url', 'max:4096'],
            'media_allowed_extensions' => ['required', 'string', 'max:255'],
            'media_max_size' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
