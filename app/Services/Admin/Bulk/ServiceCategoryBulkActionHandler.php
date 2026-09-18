<?php

namespace App\Services\Admin\Bulk;

use App\Models\ServiceCategory;
use App\Services\ServiceCatalogAdminService;
use Illuminate\Validation\ValidationException;

class ServiceCategoryBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(
        private readonly GenericBulkActionHandler $generic,
        private readonly ServiceCatalogAdminService $catalog,
    ) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        if ($action !== 'delete') {
            return $this->generic->execute($resource, $action, $ids);
        }

        $categories = ServiceCategory::query()->whereKey($ids)->get();

        if ($categories->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều danh mục dịch vụ không còn khả dụng.',
            ]);
        }

        foreach ($categories as $category) {
            $this->catalog->deleteCategory($category);
        }

        return 'Đã xóa các danh mục dịch vụ được chọn.';
    }
}
