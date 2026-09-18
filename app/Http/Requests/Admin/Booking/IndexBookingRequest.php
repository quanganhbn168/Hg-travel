<?php

namespace App\Http\Requests\Admin\Booking;

use App\Http\Requests\Admin\Concerns\HasAdminIndexPagination;
use Illuminate\Foundation\Http\FormRequest;

class IndexBookingRequest extends FormRequest
{
    use HasAdminIndexPagination;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,confirmed,cancelled,completed'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => $this->perPageRules(),
        ];
    }
}
