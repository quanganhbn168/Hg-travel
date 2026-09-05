<?php

namespace App\Casts;

use App\Services\MediaReferenceService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** The media ID is authoritative; the old path remains a backwards-compatible read fallback. */
class ManagedImage implements CastsAttributes
{
    public function __construct(private string $idColumn) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value !== $model->getRawOriginal($key) || empty($attributes[$this->idColumn])) {
            return $value;
        }
        $references = app(MediaReferenceService::class);
        $media = $references->find('media:'.$attributes[$this->idColumn]);

        return $media ? $references->originalPath($media) : $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
