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
    protected function baseRules(): array { return ['tour_category_id' => ['nullable', 'integer', 'exists:tour_categories,id'], 'destination_id' => ['nullable', 'integer', 'exists:destinations,id'], 'name' => ['required', 'string', 'max:255'], 'summary' => ['nullable', 'string'], 'description' => ['nullable', 'string'], 'duration_days' => ['required', 'integer', 'min:1'], 'duration_nights' => ['nullable', 'integer', 'min:0'], 'starting_price' => ['required', 'numeric', 'min:0'], 'currency' => ['required', 'string', 'size:3'], 'max_guests' => ['nullable', 'integer', 'min:1'], 'status' => ['required', 'in:draft,published,archived'], 'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'booking_open' => ['nullable', 'boolean'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'published_at' => ['nullable', 'date'], 'sort_order' => ['nullable', 'integer', 'min:0']]; }
}
