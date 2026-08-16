<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateProductLineRequest extends StoreProductLineRequest
{
    public function rules(): array
    {
        $productLine = $this->route('productLine');

        return $this->baseRules() + [
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_lines', 'slug')->ignore($productLine), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $productLine->getMorphClass())->orWhere('sluggable_id', '!=', $productLine->getKey())))],
        ];
    }
}
