<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportTourPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_file' => ['required', 'file', 'mimes:zip', 'max:102400'],
            'confirm_package_import' => ['accepted'],
        ];
    }
}
