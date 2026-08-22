<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : Str::slug((string) $this->input('name'))]); }
    public function rules(): array { return $this->baseRules() + ['code' => ['required', 'string', 'max:50', Rule::unique('tours', 'code')], 'slug' => ['required', 'string', 'max:255', Rule::unique('tours', 'slug'), Rule::unique('slugs', 'slug')->where(fn ($q) => $q->where('locale', app()->getLocale()))]]; }
    protected function baseRules(): array
    {
        return [
            'tour_category_ids' => ['nullable', 'array'],
            'tour_category_ids.*' => ['integer', 'distinct', 'exists:tour_categories,id'],
            'destination_ids' => ['nullable', 'array'],
            'destination_ids.*' => ['integer', 'distinct', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_nights' => ['nullable', 'integer', 'min:0'],
            'transport' => ['nullable', 'string', 'max:120'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'max_guests' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'booking_open' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'cover_image_remove' => ['nullable', 'boolean'],
            'cover_image_id' => ['nullable', 'integer'],
            'gallery_images' => ['nullable', 'string', 'max:24576'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'distinct'],
            'itineraries' => ['nullable', 'array'],
            'itineraries.*.day_number' => ['required', 'integer', 'min:1', 'distinct'],
            'itineraries.*.title' => ['required', 'string', 'max:255'],
            'itineraries.*.description' => ['nullable', 'string'],
            'itineraries.*.meals' => ['nullable', 'string', 'max:255'],
            'itineraries.*.accommodation' => ['nullable', 'string', 'max:255'],
            'schedules' => ['nullable', 'array'],
            'schedules.*.id' => ['nullable', 'integer'],
            'schedules.*.departure_date' => ['required', 'date'],
            'schedules.*.return_date' => ['nullable', 'date', 'after_or_equal:schedules.*.departure_date'],
            'schedules.*.seats_total' => ['required', 'integer', 'min:0', 'max:65535'],
            'schedules.*.price' => ['required', 'numeric', 'min:0'],
            'schedules.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'schedules.*.transport' => ['nullable', 'string', 'max:120'],
            'schedules.*.status' => ['required', 'in:open,closed,cancelled'],
            'schedules.*.notes' => ['nullable', 'string', 'max:3000'],
            'schedules.*.remove' => ['nullable', 'boolean'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.type' => ['nullable', 'string', 'max:50'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.content' => ['nullable', 'string'],
            'sections.*.remove' => ['nullable', 'boolean'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.*.type' => ['required', 'in:included,excluded'],
            'inclusions.*.content' => ['required', 'string', 'max:255'],
        ];
    }
}
