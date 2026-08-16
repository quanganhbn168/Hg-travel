<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]); }
    public function rules(): array { return ['service_category_id' => ['required', 'integer', 'exists:service_categories,id'], 'name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', Rule::unique('services'), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale()))], 'icon' => ['nullable', 'string', 'max:100'], 'description' => ['nullable', 'string'], 'intro' => ['nullable', 'string'], 'benefits' => ['nullable', 'string'], 'cover_image' => ['nullable', 'string', 'max:4096'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]; }
}
