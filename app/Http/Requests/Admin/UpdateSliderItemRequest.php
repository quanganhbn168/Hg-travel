<?php

namespace App\Http\Requests\Admin;

class UpdateSliderItemRequest extends StoreSliderItemRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:4096'],
            'image_path_remove' => ['nullable', 'boolean'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
