<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateTourRequest extends StoreTourRequest
{
    public function rules(): array
    {
        $tour = $this->route('tour');
        return $this->baseRules() + ['code' => ['required', 'string', 'max:50', Rule::unique('tours', 'code')->ignore($tour)], 'slug' => ['required', 'string', 'max:255', Rule::unique('tours', 'slug')->ignore($tour), Rule::unique('slugs', 'slug')->where(fn ($q) => $q->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $tour->getMorphClass())->orWhere('sluggable_id', '!=', $tour->getKey())))]];
    }
}
