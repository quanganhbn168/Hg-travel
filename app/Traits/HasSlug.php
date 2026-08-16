<?php

namespace App\Traits;

use App\Models\Slug;
use App\Observers\SlugObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::whenBooted(fn () => static::observe(SlugObserver::class));
    }

    public function getSlugSourceKey(): string
    {
        return 'slug';
    }

    public function slugs(): MorphMany
    {
        return $this->morphMany(Slug::class, 'sluggable');
    }

    public function getSlug(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($this->relationLoaded('slugs')) {
            return $this->slugs->firstWhere('locale', $locale)?->slug;
        }

        return $this->slugs()->where('locale', $locale)->value('slug');
    }

    public function getSlugAttribute(mixed $value = null): ?string
    {
        return is_string($value) && $value !== '' ? $value : $this->getSlug();
    }
}
