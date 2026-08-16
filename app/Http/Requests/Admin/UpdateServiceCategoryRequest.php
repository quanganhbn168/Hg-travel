<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends StoreServiceCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('serviceCategory');
        $rules = parent::rules();
        $rules['slug'] = ['required', 'string', 'max:255', Rule::unique('service_categories', 'slug')->ignore($category), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $category->getMorphClass())->orWhere('sluggable_id', '!=', $category->getKey())))];
        return $rules;
    }
}
