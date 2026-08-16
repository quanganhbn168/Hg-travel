<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminIndexRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resource = (string) $this->input('resource');

        return [
            'resource' => ['required', Rule::in(AdminIndexRegistry::resources())],
            'action' => ['required', Rule::in(array_keys(AdminIndexRegistry::bulkActionsFor($resource)))],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists(AdminIndexRegistry::tableFor($resource), 'id')],
        ];
    }
}
