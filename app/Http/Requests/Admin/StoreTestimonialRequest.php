<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['customer_name' => ['required', 'string', 'max:255'], 'customer_title' => ['nullable', 'string', 'max:255'], 'content' => ['required', 'string', 'max:5000'], 'rating' => ['required', 'integer', 'between:1,5'], 'avatar_path' => ['nullable', 'string', 'max:4096'], 'avatar_path_remove' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']];
    }
}
