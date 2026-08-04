<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function index(): View
    {
        return view('admin.media.index', ['media' => $this->mediaService->library()]);
    }

    public function list(): JsonResponse
    {
        return response()->json(['data' => $this->mediaService->library(100)->through(fn ($item) => $this->mediaService->item($item))->items()]);
    }

    public function uploadTemp(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->mediaService->upload($request->file('file'));
        $item = $this->mediaService->item($media);

        return response()->json(['success' => true, 'path' => $item['url'], 'url' => $item['url'], 'media' => $item]);
    }

    public function uploadEditor(UploadMediaRequest $request): JsonResponse
    {
        $media = $this->mediaService->upload($request->file('file'));

        return response()->json(['location' => $media->getUrl()]);
    }
}
