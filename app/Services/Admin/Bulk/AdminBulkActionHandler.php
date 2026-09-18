<?php

namespace App\Services\Admin\Bulk;

interface AdminBulkActionHandler
{
    /** @param array<int, int> $ids */
    public function execute(string $resource, string $action, array $ids): string;
}
