<?php

namespace App\Services;

use App\Models\SiteAsset;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function library(int $perPage = 24)
    {
        return Media::query()->latest('id')->paginate($perPage);
    }

    public function upload(UploadedFile $file): Media
    {
        return SiteAsset::firstOrCreate(['key' => 'library'])
            ->addMedia($file)
            ->toMediaCollection('library');
    }

    public function item(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => $media->getUrl(),
            'created_at' => $media->created_at?->toDateTimeString(),
        ];
    }
}
