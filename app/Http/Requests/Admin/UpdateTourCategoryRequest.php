<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateTourCategoryRequest extends StoreTourCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('tourCategory');
        return $this->baseRules() + ['slug' => ['required', 'string', 'max:255', Rule::unique('tour_categories', 'slug')->ignore($category), Rule::unique('slugs', 'slug')->where(fn ($q) => $q->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $category->getMorphClass())->orWhere('sluggable_id', '!=', $category->getKey())))]];
    }
}
