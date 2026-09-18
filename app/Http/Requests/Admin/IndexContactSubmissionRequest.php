<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexContactSubmissionRequest extends FormRequest
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
            'status' => ['nullable', 'in:new,processing,resolved,closed'],
            'per_page' => $this->perPageRules(),
        ];
    }
}
