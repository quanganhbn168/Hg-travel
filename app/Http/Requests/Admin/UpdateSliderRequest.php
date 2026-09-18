<?php

namespace App\Http\Requests\Admin;

class UpdateSliderRequest extends StoreSliderRequest
{
    protected function prepareForValidation(): void
    {
        $slider = $this->route('slider');

        $this->merge([
            'key' => (string) $slider?->key,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
