<?php

namespace App\Services\Admin\Bulk;

use App\Support\AdminIndexRegistry;
use Illuminate\Validation\ValidationException;

class GenericBulkActionHandler implements AdminBulkActionHandler
{
    public function execute(string $resource, string $action, array $ids): string
    {
        $modelClass = AdminIndexRegistry::modelFor($resource);
        $label = AdminIndexRegistry::labelFor($resource);

        if (! $modelClass) {
            throw new \InvalidArgumentException("Unknown admin resource [{$resource}].");
        }

        $statusUpdate = AdminIndexRegistry::statusUpdatesFor($resource)[$action] ?? null;

        if ($statusUpdate) {
            $attributes = $statusUpdate['attributes'] ?? ['status' => $statusUpdate['value']];
            $modelClass::query()->whereKey($ids)->update($attributes);

            return $statusUpdate['message'];
        }

        $records = $modelClass::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều bản ghi không còn khả dụng.',
            ]);
        }

        if ($action === 'delete') {
            $records->each->delete();

            return "Đã xóa các {$label} được chọn.";
        }

        if (! in_array($action, ['activate', 'deactivate'], true)) {
            throw ValidationException::withMessages([
                'action' => 'Thao tác hàng loạt không được hỗ trợ.',
            ]);
        }

        $modelClass::query()
            ->whereKey($ids)
            ->update(['is_active' => $action === 'activate']);

        return $action === 'activate'
            ? "Đã kích hoạt các {$label} được chọn."
            : "Đã ngừng kích hoạt các {$label} được chọn.";
    }
}
