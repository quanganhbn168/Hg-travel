<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmTourScheduleImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mappings' => ['required', 'array'],
            'mappings.*' => ['nullable', 'integer', 'exists:tours,id'],
            'confirm_schedule_import' => ['accepted'],
        ];
    }
}
