<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\AboutPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __construct(private readonly AboutPageService $about) {}

    public function edit(): View
    {
        $about = $this->about->current();

        return view('admin.about.edit', [
            'about' => $about,
            'profileContent' => $this->about->profileContent($about),
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
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
            'support_service_ids.*' => ['integer', 'exists:services,id'],
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
        ]);

        foreach (['hero_image', 'background_image', 'story_image'] as $field) {
            if ($request->boolean($field.'_remove')) {
                $data[$field] = null;
            } elseif (blank($data[$field] ?? null)) {
                unset($data[$field]);
            }

            unset($data[$field.'_remove']);
        }

        $data['core_values'] = $this->entries($data['core_values_text'] ?? null, ['title', 'description']);
        $data['markets'] = $this->entries($data['markets_text'] ?? null, ['name', 'detail']);
        $data['commitments'] = $this->lines($data['commitments_text'] ?? null);
        $data['audiences'] = $this->lines($data['audiences_text'] ?? null);
        $data['profile_content'] = [
            'hero' => ['kicker' => $data['hero_kicker'] ?? '', 'cta_label' => $data['hero_cta_label'] ?? ''],
            'letter' => ['signature_name' => $data['letter_signature_name'] ?? '', 'signature_tagline' => $data['letter_signature_tagline'] ?? ''],
            'company_intro' => [
                'eyebrow' => $data['intro_eyebrow'] ?? '',
                'title' => $data['intro_title'] ?? '',
                'lead' => $data['intro_lead'] ?? '',
                'content' => $data['intro_content'] ?? '',
                'credentials' => $this->lines($data['credentials_text'] ?? null),
            ],
            'story' => [
                'eyebrow' => $data['story_eyebrow'] ?? '',
                'fact' => $data['story_fact'] ?? '',
                'photo_alt' => $data['story_photo_alt'] ?? '',
                'photo_caption' => $data['story_photo_caption'] ?? '',
            ],
            'story_steps' => [
                'eyebrow' => $data['story_steps_eyebrow'] ?? '',
                'intro' => $data['story_steps_intro'] ?? '',
                'items' => $this->entries($data['story_steps_text'] ?? null, ['title', 'description']),
            ],
            'values' => ['eyebrow' => $data['values_eyebrow'] ?? '', 'title' => $data['values_title'] ?? '', 'intro' => $data['values_intro'] ?? ''],
            'featured_products' => [
                'eyebrow' => $data['products_eyebrow'] ?? '',
                'title' => $data['products_title'] ?? '',
                'intro' => $data['products_intro'] ?? '',
                'items' => $this->entries($data['products_text'] ?? null, ['icon', 'title', 'description']),
            ],
            'support' => [
                'eyebrow' => $data['support_eyebrow'] ?? '',
                'title' => $data['support_title'] ?? '',
                'intro' => $data['support_intro'] ?? '',
                'service_ids' => collect($data['support_service_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all(),
            ],
            'leaders' => [
                'eyebrow' => $data['leaders_eyebrow'] ?? '',
                'title' => $data['leaders_title'] ?? '',
                'intro' => $data['leaders_intro'] ?? '',
                'ceo_role' => $data['ceo_role'] ?? '',
                'deputy_role' => $data['deputy_role'] ?? '',
            ],
            'clients' => [
                'eyebrow' => $data['clients_eyebrow'] ?? '',
                'title' => $data['clients_title'] ?? '',
                'intro' => $data['clients_intro'] ?? '',
                'items' => $this->entries($data['clients_text'] ?? null, ['name', 'image']),
            ],
            'organisation' => [
                'eyebrow' => $data['organisation_eyebrow'] ?? '',
                'title' => $data['organisation_title'] ?? '',
                'intro' => $data['organisation_intro'] ?? '',
                'cta_label' => $data['organisation_cta_label'] ?? '',
                'departments' => $this->lines($data['departments_text'] ?? null),
                'offices' => $this->entries($data['offices_text'] ?? null, ['icon', 'label', 'address']),
            ],
            'contact' => ['tagline' => $data['contact_tagline'] ?? ''],
        ];

        unset($data['core_values_text'], $data['markets_text'], $data['commitments_text'], $data['audiences_text']);
        unset($data['hero_kicker'], $data['hero_cta_label'], $data['letter_signature_name'], $data['letter_signature_tagline']);
        unset($data['intro_eyebrow'], $data['intro_title'], $data['intro_lead'], $data['intro_content'], $data['credentials_text']);
        unset($data['story_eyebrow'], $data['story_fact'], $data['story_photo_alt'], $data['story_photo_caption'], $data['story_steps_eyebrow'], $data['story_steps_intro'], $data['story_steps_text']);
        unset($data['values_eyebrow'], $data['values_title'], $data['values_intro']);
        unset($data['products_eyebrow'], $data['products_title'], $data['products_intro'], $data['products_text']);
        unset($data['support_eyebrow'], $data['support_title'], $data['support_intro'], $data['support_service_ids']);
        unset($data['leaders_eyebrow'], $data['leaders_title'], $data['leaders_intro'], $data['ceo_role'], $data['deputy_role']);
        unset($data['clients_eyebrow'], $data['clients_title'], $data['clients_intro'], $data['clients_text']);
        unset($data['organisation_eyebrow'], $data['organisation_title'], $data['organisation_intro'], $data['organisation_cta_label'], $data['departments_text'], $data['offices_text'], $data['contact_tagline']);

        $this->about->update($data);

        return back()->with('success', 'Đã cập nhật trang Giới thiệu.');
    }

    /** @return array<int, string> */
    private function lines(?string $value): array
    {
        return collect(preg_split('/\R/u', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<int, string> $keys
     *  @return array<int, array<string, string>> */
    private function entries(?string $value, array $keys): array
    {
        return collect($this->lines($value))
            ->map(function (string $line) use ($keys): array {
                $parts = array_map('trim', explode('|', $line, count($keys)));

                return array_combine($keys, array_pad($parts, count($keys), ''));
            })
            ->filter(fn (array $entry) => filled(reset($entry)))
            ->values()
            ->all();
    }
}
