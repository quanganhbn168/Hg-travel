<?php

namespace App\Http\Requests\Admin\User;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexUserRequest extends FormRequest
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
            'per_page' => $this->perPageRules(),
        ];
    }
}
