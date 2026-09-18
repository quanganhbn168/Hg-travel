<?php

namespace App\Services\Admin\Bulk;

use App\Models\Destination;
use App\Services\DestinationService;
use Illuminate\Validation\ValidationException;

class DestinationBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(private readonly DestinationService $destinations) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        $records = Destination::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều điểm đến không còn khả dụng.',
            ]);
        }

        if ($records->contains(fn (Destination $destination): bool => $destination->is_system)) {
            throw ValidationException::withMessages([
                'ids' => 'Nhóm địa lý hệ thống không thể thay đổi bằng thao tác hàng loạt.',
            ]);
        }

        if ($action === 'delete') {
            foreach ($records as $destination) {
                $this->destinations->delete($destination);
            }

            return 'Đã xóa các điểm đến được chọn.';
        }

        if (! in_array($action, ['activate', 'deactivate'], true)) {
            throw ValidationException::withMessages([
                'action' => 'Thao tác điểm đến không hợp lệ.',
            ]);
        }

        Destination::query()
            ->whereKey($ids)
            ->update(['is_active' => $action === 'activate']);

        return $action === 'activate'
            ? 'Đã kích hoạt các điểm đến được chọn.'
            : 'Đã ngừng kích hoạt các điểm đến được chọn.';
    }
}
