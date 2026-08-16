<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_email_secondary' => ['nullable', 'email', 'max:255'],
            'contact_email_tertiary' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_phone_secondary' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'facebook_url' => ['nullable', 'url', 'max:4096'],
            'instagram_url' => ['nullable', 'url', 'max:4096'],
            'youtube_url' => ['nullable', 'url', 'max:4096'],
            'zalo_url' => ['nullable', 'url', 'max:4096'],
            'messenger_url' => ['nullable', 'url', 'max:4096'],
            'whatsapp_url' => ['nullable', 'url', 'max:4096'],
        ];
    }
}
