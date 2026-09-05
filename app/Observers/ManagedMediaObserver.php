<?php

namespace App\Observers;

use App\Services\MediaReferenceService;
use App\Support\MediaFields;
use Illuminate\Database\Eloquent\Model;

final class ManagedMediaObserver
{
    public function saving(Model $model): void
    {
        $references = app(MediaReferenceService::class);
        foreach (MediaFields::IMAGES[$model::class] ?? [] as $field => $idColumn) {
            if (! $model->isDirty($field)) {
                continue;
            }
            $resolved = $references->resolve($model->getAttribute($field), $field);
            $model->setAttribute($field, $resolved['path']);
            $model->setAttribute($idColumn, $resolved['id']);
        }
        foreach (MediaFields::CONTENT[$model::class] ?? [] as $field) {
            if ($model->isDirty($field)) {
                $model->setAttribute($field, $references->content($model->getAttribute($field)));
            }
        }
    }
}
