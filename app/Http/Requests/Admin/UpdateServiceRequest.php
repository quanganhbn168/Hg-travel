<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateServiceRequest extends StoreServiceRequest
{
    public function rules(): array
    {
        $service = $this->route('service');
        $rules = parent::rules();
        $rules['slug'] = ['required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($service), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $service->getMorphClass())->orWhere('sluggable_id', '!=', $service->getKey())))];
        return $rules;
    }
}
