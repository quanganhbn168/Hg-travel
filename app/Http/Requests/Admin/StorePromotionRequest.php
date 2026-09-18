<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'tour_ids' => ['nullable', 'array'],
            'tour_ids.*' => ['integer', 'distinct', 'exists:tours,id'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (
                $this->input('discount_type') === 'percentage'
                && (float) $this->input('discount_value', 0) > 100
            ) {
                $validator->errors()->add(
                    'discount_value',
                    'Mức giảm phần trăm không thể lớn hơn 100%.',
                );
            }
        }];
    }
}
