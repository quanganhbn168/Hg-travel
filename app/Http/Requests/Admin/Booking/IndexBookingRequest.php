<?php

namespace App\Http\Requests\Admin\Booking;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['status' => ['nullable', 'in:pending,confirmed,cancelled,completed'], 'search' => ['nullable', 'string', 'max:255'], 'per_page' => ['nullable', 'integer', 'in:10,20,25,50']]; }
}
