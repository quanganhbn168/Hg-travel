<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_schedules' => ['nullable', 'boolean'],
            'show_seat_availability' => ['nullable', 'boolean'],
            'schedule_note' => ['required', 'string', 'max:500'],
        ];
    }
}
