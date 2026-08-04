<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]); }
    public function rules(): array { return ['template' => ['required', 'string', 'max:100'], 'name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', Rule::unique('pages'), Rule::unique('slugs', 'slug')->where('locale', app()->getLocale())], 'sub_title' => ['nullable', 'string', 'max:255'], 'content' => ['nullable', 'string'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'seo_keywords' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'published_at' => ['nullable', 'date']]; }
}
