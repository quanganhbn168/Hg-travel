<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_intro' => ['nullable', 'string', 'max:1000'],
            'letter_title' => ['nullable', 'string', 'max:255'],
            'letter_content' => ['nullable', 'string'],
            'story_title' => ['nullable', 'string', 'max:255'],
            'story_content' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'markets_eyebrow' => ['nullable', 'string', 'max:255'],
            'markets_title' => ['nullable', 'string', 'max:1000'],
            'markets_intro' => ['nullable', 'string', 'max:1000'],
            'core_values_text' => ['nullable', 'string'],
            'markets_text' => ['nullable', 'string'],
            'commitments_text' => ['nullable', 'string'],
            'audiences_text' => ['nullable', 'string'],
            'ceo_name' => ['nullable', 'string', 'max:255'],
            'ceo_bio' => ['nullable', 'string'],
            'deputy_name' => ['nullable', 'string', 'max:255'],
            'deputy_bio' => ['nullable', 'string'],
            'background_image' => ['nullable', 'string', 'max:4096'],
            'background_image_remove' => ['nullable', 'boolean'],
            'hero_image' => ['nullable', 'string', 'max:4096'],
            'hero_image_remove' => ['nullable', 'boolean'],
            'story_image' => ['nullable', 'string', 'max:4096'],
            'story_image_remove' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'hero_kicker' => ['nullable', 'string', 'max:255'],
            'hero_cta_label' => ['nullable', 'string', 'max:255'],
            'letter_signature_name' => ['nullable', 'string', 'max:255'],
            'letter_signature_tagline' => ['nullable', 'string', 'max:255'],
            'intro_eyebrow' => ['nullable', 'string', 'max:255'],
            'intro_title' => ['nullable', 'string', 'max:1000'],
            'intro_lead' => ['nullable', 'string', 'max:1000'],
            'intro_content' => ['nullable', 'string', 'max:1000'],
            'credentials_text' => ['nullable', 'string'],
            'story_eyebrow' => ['nullable', 'string', 'max:255'],
            'story_fact' => ['nullable', 'string', 'max:1000'],
            'story_photo_alt' => ['nullable', 'string', 'max:255'],
            'story_photo_caption' => ['nullable', 'string', 'max:1000'],
            'story_steps_eyebrow' => ['nullable', 'string', 'max:255'],
            'story_steps_intro' => ['nullable', 'string', 'max:1000'],
            'story_steps_text' => ['nullable', 'string'],
            'values_eyebrow' => ['nullable', 'string', 'max:255'],
            'values_title' => ['nullable', 'string', 'max:1000'],
            'values_intro' => ['nullable', 'string', 'max:1000'],
            'products_eyebrow' => ['nullable', 'string', 'max:255'],
            'products_title' => ['nullable', 'string', 'max:1000'],
            'products_intro' => ['nullable', 'string', 'max:1000'],
            'products_text' => ['nullable', 'string'],
            'support_eyebrow' => ['nullable', 'string', 'max:255'],
            'support_title' => ['nullable', 'string', 'max:1000'],
            'support_intro' => ['nullable', 'string', 'max:1000'],
            'support_service_ids' => ['nullable', 'array'],
            'support_service_ids.*' => ['integer', 'distinct', 'exists:services,id'],
            'leaders_eyebrow' => ['nullable', 'string', 'max:255'],
            'leaders_title' => ['nullable', 'string', 'max:1000'],
            'leaders_intro' => ['nullable', 'string', 'max:1000'],
            'ceo_role' => ['nullable', 'string', 'max:255'],
            'deputy_role' => ['nullable', 'string', 'max:255'],
            'clients_eyebrow' => ['nullable', 'string', 'max:255'],
            'clients_title' => ['nullable', 'string', 'max:1000'],
            'clients_intro' => ['nullable', 'string', 'max:1000'],
            'clients_text' => ['nullable', 'string'],
            'organisation_eyebrow' => ['nullable', 'string', 'max:255'],
            'organisation_title' => ['nullable', 'string', 'max:1000'],
            'organisation_intro' => ['nullable', 'string', 'max:1000'],
            'organisation_cta_label' => ['nullable', 'string', 'max:255'],
            'departments_text' => ['nullable', 'string'],
            'offices_text' => ['nullable', 'string'],
            'contact_tagline' => ['nullable', 'string', 'max:255'],
        ];
    }
}
