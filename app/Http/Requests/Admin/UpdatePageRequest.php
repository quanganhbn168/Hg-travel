<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdatePageRequest extends StorePageRequest
{
    public function rules(): array
    {
        $page = $this->route('page');
        return array_merge(parent::rules(), ['slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale())->where(fn ($inner) => $inner->where('sluggable_type', '!=', $page->getMorphClass())->orWhere('sluggable_id', '!=', $page->getKey())))]]);
    }
}
