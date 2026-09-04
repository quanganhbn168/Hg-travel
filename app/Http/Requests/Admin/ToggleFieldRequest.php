<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminIndexRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource' => ['required', 'string', Rule::in(AdminIndexRegistry::resources())],
            'id' => ['required', 'integer', 'min:1'],
            'field' => ['required', 'string'],
            'value' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $resource = (string) $this->input('resource');
            $field = (string) $this->input('field');

            if ($resource !== '' && ! in_array($field, AdminIndexRegistry::toggleFieldsFor($resource), true)) {
                $validator->errors()->add('field', 'Trường bật/tắt không hợp lệ.');
            }
        }];
    }
}
