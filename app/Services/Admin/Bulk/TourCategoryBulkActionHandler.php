<?php

namespace App\Services\Admin\Bulk;

use App\Models\TourCategory;
use App\Services\TourCategoryService;
use Illuminate\Validation\ValidationException;

class TourCategoryBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(
        private readonly GenericBulkActionHandler $generic,
        private readonly TourCategoryService $categories,
    ) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        if ($action !== 'delete') {
            return $this->generic->execute($resource, $action, $ids);
        }

        $records = TourCategory::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều loại hình tour không còn khả dụng.',
            ]);
        }

        foreach ($records as $category) {
            $this->categories->delete($category);
        }

        return 'Đã xóa các loại hình tour được chọn.';
    }
}
