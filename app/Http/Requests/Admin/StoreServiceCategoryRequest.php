<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]); }
    public function rules(): array { return ['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', Rule::unique('service_categories'), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale()))], 'kicker' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'icon' => ['nullable', 'string', 'max:100'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]; }
}
