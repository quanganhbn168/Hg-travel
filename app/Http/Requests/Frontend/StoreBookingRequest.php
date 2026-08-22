<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
            'tour_schedule_id' => ['nullable', 'integer', 'exists:tour_schedules,id'],
            'departure_date' => ['nullable', 'date', Rule::requiredIf(fn (): bool => $this->input('source') === 'tour_detail' && ! $this->filled('tour_schedule_id'))],
            'adults' => ['required', 'integer', 'min:1', 'max:100'],
            'children' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'source' => ['nullable', 'in:booking_page,tour_detail'],
        ];
    }
}
