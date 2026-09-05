<?php

namespace App\Services;

use App\Jobs\OptimizeMedia;
use App\Models\SiteAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function library(int $perPage = 24, string $search = '', bool $imagesOnly = false, bool $archived = false)
    {
        return Media::query()
            ->when($archived, fn ($query) => $query->where('custom_properties->state', 'archived'), fn ($query) => $query->where(fn ($visible) => $visible->whereNull('custom_properties->state')->orWhere('custom_properties->state', 'ready')->orWhere(fn ($pending) => $pending->where('custom_properties->state', 'pending')->where('custom_properties->uploaded_by', auth('admin')->id()))))
            ->when($imagesOnly, fn ($query) => $query->where('mime_type', 'like', 'image/%'))
            ->when($search !== '', fn ($query) => $query->where(fn ($searchQuery) => $searchQuery->where('name', 'like', '%'.trim($search).'%')->orWhere('custom_properties->imported_path', 'like', '%'.trim($search).'%')))
            ->latest('id')->paginate(min(100, max(1, $perPage)))->withQueryString();
    }

    public function upload(UploadedFile $file, bool $pending = true): Media
    {
        Validator::validate(['file' => $file], ['file' => app(MediaPolicy::class)->rules(false)]);
        $dimensions = app(MediaPolicy::class)->inspect($file);
        $media = SiteAsset::firstOrCreate(['key' => 'library'])
            ->addMedia($file)
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName(Str::uuid().'.'.$file->guessExtension())
            ->withCustomProperties($dimensions + ['state' => $pending ? 'pending' : 'ready', 'uploaded_by' => auth('admin')->id(), 'original_name' => $file->getClientOriginalName()])
            ->toMediaCollection('library');
        app(MediaReferenceService::class)->forget();
        OptimizeMedia::dispatch($media->id);

        return $media;
    }

    public function item(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->getCustomProperty('imported_path') ? basename(dirname($media->getCustomProperty('imported_path'))).' · '.$media->file_name : $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => app(MediaReferenceService::class)->url('media:'.$media->id),
            'original_url' => '/'.app(MediaReferenceService::class)->originalPath($media),
            'thumbnail_url' => app(MediaReferenceService::class)->url('media:'.$media->id, thumbnail: true),
            'reference' => 'media:'.$media->id,
            'state' => $media->getCustomProperty('state', 'ready'),
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
            'created_at' => $media->created_at?->toDateTimeString(),
        ];
    }

    public function archive(Media $media): void
    {
        DB::transaction(function () use ($media): void {
            $media = Media::whereKey($media->id)->lockForUpdate()->firstOrFail();
            $usages = app(MediaUsageService::class)->usages($media);
            if ($usages !== []) {
                throw ValidationException::withMessages(['media' => 'Tệp đang được sử dụng: '.implode(', ', array_map(fn ($usage) => app(MediaUsageService::class)->describe($usage), $usages))]);
            }
            $media->setCustomProperty('state', 'archived')->save();
        });
    }

    public function restore(Media $media): void
    {
        DB::transaction(function () use ($media): void {
            $media = Media::whereKey($media->id)->lockForUpdate()->firstOrFail();
            if ($media->getCustomProperty('state') !== 'archived') {
                throw ValidationException::withMessages(['media' => 'Chỉ khôi phục tệp đã lưu trữ.']);
            }
            if (! Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
                throw ValidationException::withMessages(['media' => 'File gốc không còn trên máy chủ. Hãy phục hồi từ bản sao lưu.']);
            }
            $media->setCustomProperty('state', 'ready')->save();
        });
    }
}
