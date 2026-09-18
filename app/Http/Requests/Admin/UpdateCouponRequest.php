<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCouponRequest extends StoreCouponRequest
{
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return $this->baseRules() + [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
        ];
    }
}
