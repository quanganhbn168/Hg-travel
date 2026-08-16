<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductLineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : Str::slug((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return $this->baseRules() + [
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_lines', 'slug'), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale()))],
        ];
    }

    protected function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kicker' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:1000'],
            'description' => ['required', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'string', 'max:4096'],
            'hero_image' => ['nullable', 'string', 'max:4096'],
            'benefits' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_home' => ['nullable', 'boolean'],
            'tours' => ['nullable', 'array'],
            'tours.*' => ['integer', 'exists:tours,id'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ];
    }
}
