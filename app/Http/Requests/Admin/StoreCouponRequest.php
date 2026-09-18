<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
        ]);
    }

    public function rules(): array
    {
        return $this->baseRules() + [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code'),
            ],
        ];
    }

    protected function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'minimum_booking_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
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
