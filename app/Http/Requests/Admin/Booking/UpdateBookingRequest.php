<?php

namespace App\Http\Requests\Admin\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['status' => ['required', 'in:pending,confirmed,cancelled,completed'], 'payment_status' => ['required', 'in:unpaid,pending,paid,refunded'], 'notes' => ['nullable', 'string']]; }
}
