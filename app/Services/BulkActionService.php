<?php

namespace App\Services;

use App\Services\Admin\Bulk\AdminBulkActionHandler;
use App\Services\Admin\Bulk\GenericBulkActionHandler;
use App\Support\AdminIndexRegistry;
use Illuminate\Support\Str;

class BulkActionService
{
    public function __construct(private readonly GenericBulkActionHandler $generic) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        return $this->handlerFor($resource)->execute($resource, $action, $ids);
    }

    private function handlerFor(string $resource): AdminBulkActionHandler
    {
        $class = 'App\\Services\\Admin\\Bulk\\'.Str::studly($resource).'BulkActionHandler';

        return class_exists($class)
            ? app($class)
            : $this->generic;
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
