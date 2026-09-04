<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : Str::slug((string) $this->input('name'))]); }
    public function rules(): array { return $this->baseRules() + ['slug' => ['required', 'string', 'max:255', Rule::unique('destinations', 'slug'), Rule::unique('slugs', 'slug')->where(fn ($q) => $q->where('locale', app()->getLocale()))]]; }
    protected function baseRules(): array { return ['parent_id' => ['nullable', 'integer', Rule::exists('destinations', 'id')->where(fn ($query) => $query->where('is_system', true)->where('is_active', true))], 'name' => ['required', 'string', 'max:255'], 'summary' => ['nullable', 'string'], 'description' => ['nullable', 'string'], 'cover_image' => ['nullable', 'string', 'max:4096'], 'cover_image_remove' => ['nullable', 'boolean'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]; }
}
