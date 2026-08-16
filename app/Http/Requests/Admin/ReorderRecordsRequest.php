<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminIndexRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resource = (string) $this->input('resource');

        return [
            'resource' => ['required', Rule::in(AdminIndexRegistry::reorderResources())],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id' => ['required', 'integer', 'distinct', Rule::exists(AdminIndexRegistry::tableFor($resource), 'id')],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ];
    }
}
