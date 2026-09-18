<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTravelMomentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug')
                ? Str::slug((string) $this->input('slug'))
                : Str::slug((string) $this->input('title')),
        ]);
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', 'exists:travel_moment_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('travel_moments', 'slug')],
            'image_url' => ['required', 'string', 'max:4096'],
            'image_url_remove' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
