<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductLineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'home' => ['nullable', 'in:yes,no'],
            'per_page' => ['nullable', 'integer', 'in:15,30,50'],
        ];
    }
}
