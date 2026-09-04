<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Http\Requests\Admin\ReorderRecordsRequest;
use App\Http\Requests\Admin\ToggleFieldRequest;
use App\Services\AdminToggleService;
use App\Services\BulkActionService;
use App\Services\ReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommonController extends Controller
{
    public function __construct(
        private readonly BulkActionService $bulkActionService,
        private readonly ReorderService $reorderService,
        private readonly AdminToggleService $adminToggleService,
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

    public function toggle(ToggleFieldRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->adminToggleService->execute(
            $data['resource'],
            (int) $data['id'],
            $data['field'],
            (bool) $data['value'],
        );

        return response()->json($result);
    }
}
