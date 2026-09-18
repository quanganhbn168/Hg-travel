<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => [
                'required',
                Rule::in(['header', 'footer']),
                Rule::unique('menus', 'location'),
            ],
            'is_active' => ['nullable', 'boolean'],
            'items_present' => ['nullable', 'boolean'],
            'items_json' => ['nullable', 'string'],
        ];
    }
}
