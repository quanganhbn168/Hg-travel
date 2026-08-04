<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexTourRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:draft,published,archived'], 'active' => ['nullable', 'in:0,1'], 'per_page' => ['nullable', 'integer', 'in:10,15,25,50']]; }
}
