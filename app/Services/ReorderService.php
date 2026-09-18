<?php

namespace App\Services;

use App\Support\AdminIndexRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderService
{
    public function execute(string $resource, array $items): void
    {
        $modelClass = AdminIndexRegistry::modelFor($resource);
        $column = AdminIndexRegistry::orderColumnFor($resource);

        if (! $modelClass || ! $column) {
            throw new \InvalidArgumentException("Resource [{$resource}] cannot be reordered.");
        }

        DB::transaction(function () use ($modelClass, $column, $items): void {
            $model = new $modelClass;
            $key = $model->getKeyName();

            $selectedIds = collect($items)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values();

            $allRecords = $modelClass::query()
                ->orderBy($column)
                ->orderBy($key)
                ->lockForUpdate()
                ->get([$key, $column]);

            if ($allRecords->whereIn($key, $selectedIds)->count() !== $selectedIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Một hoặc nhiều bản ghi không còn khả dụng để sắp xếp.',
                ]);
            }

            $selectedOrder = collect($items)
                ->map(fn (array $item): int => (int) $item['id'])
                ->values();

            $selectedSet = $selectedOrder->flip();

            $remainingIds = $allRecords
                ->reject(fn ($record): bool => $selectedSet->has((int) $record->getKey()))
                ->pluck($key)
                ->map(fn ($id): int => (int) $id)
                ->values();

            $requestedStart = max(
                1,
                (int) collect($items)->min('order'),
            );

            $insertAt = min(
                $remainingIds->count(),
                $requestedStart - 1,
            );

            $orderedIds = $remainingIds->values();
            $orderedIds->splice($insertAt, 0, $selectedOrder->all());

            foreach ($orderedIds->values() as $index => $id) {
                $modelClass::query()
                    ->whereKey($id)
                    ->update([$column => $index + 1]);
            }
        });
    }

    public static function resources(): array
    {
        return AdminIndexRegistry::reorderResources();
    }

    public static function tableFor(string $resource): string
    {
        return AdminIndexRegistry::tableFor($resource);
    }
}
