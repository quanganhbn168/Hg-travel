<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexTourCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:active,inactive'], 'home' => ['nullable', 'in:yes,no'], 'per_page' => ['nullable', 'integer', 'in:10,15,25,50']]; }
}
