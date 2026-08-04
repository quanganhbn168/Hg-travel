<?php

namespace App\Http\Requests\Admin\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['customer_name' => ['required', 'string', 'max:255'], 'customer_email' => ['required', 'email', 'max:255'], 'customer_phone' => ['required', 'string', 'max:30'], 'customer_address' => ['nullable', 'string'], 'tour_id' => ['required', 'exists:tours,id'], 'departure_date' => ['nullable', 'date'], 'adults' => ['required', 'integer', 'min:1'], 'children' => ['nullable', 'integer', 'min:0'], 'unit_price' => ['required', 'numeric', 'min:0'], 'notes' => ['nullable', 'string'], 'status' => ['required', 'in:pending,confirmed,cancelled,completed'], 'payment_status' => ['required', 'in:unpaid,pending,paid,refunded']]; }
}
