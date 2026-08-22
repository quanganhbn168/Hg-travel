<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PrepareTourScheduleImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_file' => ['nullable', 'file', 'mimes:xlsx', 'max:51200', 'required_without:google_sheet_url'],
            'google_sheet_url' => ['nullable', 'url', 'max:2048', 'required_without:schedule_file'],
            'import_year' => ['required', 'integer', 'between:2020,2100'],
        ];
    }
}
