<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Services\MediaPolicy;
use App\Services\MediaReferenceService;
use App\Services\MediaService;
use App\Services\MediaUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function index(Request $request): View
    {
        return view('admin.media.index', ['media' => $this->mediaService->library(search: (string) $request->query('q', ''), archived: $request->boolean('archived'))->through(fn ($item) => $this->mediaService->item($item)), 'acceptedFiles' => implode(',', array_map(fn ($ext) => '.'.$ext, app(MediaPolicy::class)->extensions(false)))]);
    }

    public function list(Request $request): JsonResponse
    {
        $page = $this->mediaService->library(24, (string) $request->query('q', ''), true);

        return response()->json(['data' => $page->through(fn ($item) => $this->mediaService->item($item))->items(), 'next_page_url' => $page->nextPageUrl(), 'total' => $page->total()]);
    }

    public function uploadTemp(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->mediaService->upload($request->file('file'));
        $item = $this->mediaService->item($media);

        return response()->json(['success' => true, 'path' => $item['reference'], 'url' => $item['url'], 'media' => $item]);
    }

    public function uploadEditor(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->mediaService->upload($request->file('file'));

        return response()->json(['location' => '/'.app(MediaReferenceService::class)->originalPath($media), 'media' => $this->mediaService->item($media)]);
    }

    public function uploadLibrary(UploadMediaRequest $request): JsonResponse
    {
        return response()->json(['media' => $this->mediaService->item($this->mediaService->upload($request->file('file'), false))]);
    }

    public function usage(Media $media): JsonResponse
    {
        $usage = app(MediaUsageService::class);

        return response()->json(['usages' => array_map(fn ($item) => $usage->describe($item), $usage->usages($media))]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->mediaService->archive($media);

        return response()->json(['message' => 'Đã lưu trữ tệp; file gốc vẫn được giữ để khôi phục.']);
    }

    public function restore(Media $media): JsonResponse
    {
        $this->mediaService->restore($media);

        return response()->json(['message' => 'Đã khôi phục tệp vào thư viện.']);
    }
}
