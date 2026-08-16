<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\ReorderRecordsRequest;
use App\Services\BulkActionService;
use App\Services\ReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommonController extends Controller
{
    public function __construct(
        private readonly BulkActionService $bulkActionService,
        private readonly ReorderService $reorderService,
    ) {}

    public function bulkAction(BulkActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $message = $this->bulkActionService->execute($data['resource'], $data['action'], $data['ids']);

        return back()->with('success', $message);
    }

    public function reorder(ReorderRecordsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->reorderService->execute($data['resource'], $data['items']);

        return response()->json(['message' => 'Đã cập nhật thứ tự hiển thị.']);
    }
}
