<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexPostRequest extends FormRequest
{
    use HasAdminIndexPagination;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:post_categories,id'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => $this->perPageRules(),
        ];
    }
}
