<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateTravelMomentRequest extends StoreTravelMomentRequest
{
    public function rules(): array
    {
        $moment = $this->route('travelMoment');

        return [
            'group_id' => ['required', 'integer', 'exists:travel_moment_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('travel_moments', 'slug')->ignore($moment),
            ],
            'image_url' => ['nullable', 'string', 'max:4096'],
            'image_url_remove' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
