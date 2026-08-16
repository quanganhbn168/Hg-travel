<?php

namespace App\Services;

use App\Support\AdminIndexRegistry;
use Illuminate\Support\Facades\DB;

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
            foreach ($items as $item) {
                $modelClass::query()
                    ->whereKey($item['id'])
                    ->update([$column => $item['order']]);
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
