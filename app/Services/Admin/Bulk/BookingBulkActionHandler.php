<?php

namespace App\Services\Admin\Bulk;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Validation\ValidationException;

class BookingBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(private readonly BookingService $bookings) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        $status = match ($action) {
            'confirm' => 'confirmed',
            'cancel' => 'cancelled',
            'complete' => 'completed',
            default => null,
        };

        if (! $status) {
            throw ValidationException::withMessages([
                'action' => 'Thao tác booking không hợp lệ.',
            ]);
        }

        $records = Booking::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều booking không còn khả dụng.',
            ]);
        }

        foreach ($records as $booking) {
            $this->bookings->changeStatus(
                $booking,
                $status,
                'Cập nhật trạng thái hàng loạt từ trang quản trị.',
            );
        }

        return match ($action) {
            'confirm' => 'Đã xác nhận các booking được chọn.',
            'cancel' => 'Đã hủy các booking được chọn và cập nhật lại số chỗ.',
            'complete' => 'Đã hoàn tất các booking được chọn.',
        };
    }
}
