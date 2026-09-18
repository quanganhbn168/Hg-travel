<?php

namespace App\Services\Admin\Bulk;

use App\Models\PostCategory;
use App\Services\PostCategoryService;
use Illuminate\Validation\ValidationException;

class PostCategoryBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(
        private readonly GenericBulkActionHandler $generic,
        private readonly PostCategoryService $categories,
    ) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        if ($action !== 'delete') {
            return $this->generic->execute($resource, $action, $ids);
        }

        $records = PostCategory::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều danh mục bài viết không còn khả dụng.',
            ]);
        }

        foreach ($records as $category) {
            $this->categories->delete($category);
        }

        return 'Đã xóa các danh mục bài viết được chọn.';
    }
}
