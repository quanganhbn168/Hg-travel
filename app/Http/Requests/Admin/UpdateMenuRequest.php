<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateMenuRequest extends StoreMenuRequest
{
    public function rules(): array
    {
        $menu = $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => [
                'required',
                Rule::in(['header', 'footer']),
                Rule::unique('menus', 'location')->ignore($menu),
            ],
            'is_active' => ['nullable', 'boolean'],
            'items_present' => ['nullable', 'boolean'],
            'items_json' => ['nullable', 'string'],
        ];
    }
}
