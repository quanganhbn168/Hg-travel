<?php

namespace App\Services;

use App\Support\AdminIndexRegistry;

class BulkActionService
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

        $query = $modelClass::query()->whereKey($ids);

        if ($resource === 'destination') {
            $query->where('is_system', false);
        }

        if ($action === 'delete') {
            $query->get()->each->delete();

            return "Đã xóa các {$label} được chọn.";
        }

        $query->update([
            'is_active' => $action === 'activate',
        ]);

        return $action === 'activate'
            ? "Đã kích hoạt các {$label} được chọn."
            : "Đã ngừng kích hoạt các {$label} được chọn.";
    }

    public static function resources(): array
    {
        return AdminIndexRegistry::resources();
    }

    public static function tableFor(string $resource): string
    {
        return AdminIndexRegistry::tableFor($resource);
    }

    public static function actionsFor(string $resource): array
    {
        return array_keys(AdminIndexRegistry::bulkActionsFor($resource));
    }
}
