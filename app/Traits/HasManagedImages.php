<?php

namespace App\Traits;

use App\Casts\ManagedImage;
use App\Support\MediaFields;

trait HasManagedImages
{
    public function initializeHasManagedImages(): void
    {
        foreach (MediaFields::IMAGES[static::class] ?? [] as $field => $idColumn) {
            $this->mergeCasts([$field => ManagedImage::class.':'.$idColumn]);
        }
    }
}
