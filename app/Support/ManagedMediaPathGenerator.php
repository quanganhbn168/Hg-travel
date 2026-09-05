<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

final class ManagedMediaPathGenerator extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        $imported = $media->getCustomProperty('imported_path');

        return $imported ? rtrim(str_replace('\\', '/', dirname($imported)), '/').'/' : parent::getPath($media);
    }

    public function getPathForConversions(Media $media): string
    {
        return $media->id.'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $media->id.'/responsive-images/';
    }
}
