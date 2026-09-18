<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use App\Services\DestinationTreeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDestinationRequest extends FormRequest
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
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('destinations', 'id')
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'status' => ['nullable', 'in:active,inactive'],
            'type' => ['nullable', 'in:'.implode(',', array_keys(DestinationTreeService::TYPES))],
            'market' => ['nullable', 'in:'.implode(',', array_keys(DestinationTreeService::MARKETS))],
            'per_page' => $this->perPageRules(),
        ];
    }
}
