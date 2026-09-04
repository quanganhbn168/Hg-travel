<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateDestinationRequest extends StoreDestinationRequest
{
    public function rules(): array
    {
        $destination = $this->route('destination');

        if ($destination?->is_system) {
            return [
                'cover_image' => ['nullable', 'string', 'max:4096'],
                'cover_image_remove' => ['nullable', 'boolean'],
            ];
        }

        return $this->baseRules() + ['slug' => ['required', 'string', 'max:255', Rule::unique('destinations', 'slug')->ignore($destination), Rule::unique('slugs', 'slug')->where(fn ($q) => $q->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $destination->getMorphClass())->orWhere('sluggable_id', '!=', $destination->getKey())))]];
    }
}
