<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexTravelMomentRequest extends FormRequest
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
            'group_id' => ['nullable', 'integer', 'exists:travel_moment_groups,id'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => $this->perPageRules(),
        ];
    }
}
