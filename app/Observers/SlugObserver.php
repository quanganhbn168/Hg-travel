<?php

namespace App\Observers;

use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugObserver
{
    public function saving(Model $model): void
    {
        $value = $model->getAttribute('slug') ?: $model->getAttribute('name');
        $slug = Str::slug((string) $value);
        if ($slug !== '') {
            $model->setAttribute('slug', $this->unique($slug, $model));
        }
    }

    public function saved(Model $model): void
    {
        $slug = $model->getAttribute('slug');
        if ($slug) {
            $model->slugs()->updateOrCreate(['locale' => app()->getLocale()], ['slug' => $slug]);
        }
    }

    public function deleted(Model $model): void
    {
        $model->slugs()->delete();
    }

    private function unique(string $base, Model $model): string
    {
        $slug = $base;
        $suffix = 1;
        while (Slug::query()->where('slug', $slug)->where('locale', app()->getLocale())->where(function ($query) use ($model): void {
            $query->where('sluggable_type', '!=', $model->getMorphClass())->orWhere('sluggable_id', '!=', $model->getKey());
        })->exists()) {
            $slug = $base.'-'.($suffix++);
        }

        return $slug;
    }
}
