<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:255'], 'category' => ['nullable', 'integer', 'exists:service_categories,id'], 'status' => ['nullable', 'in:active,inactive'], 'per_page' => ['nullable', 'integer', 'in:10,20,25,50']]; }
}
