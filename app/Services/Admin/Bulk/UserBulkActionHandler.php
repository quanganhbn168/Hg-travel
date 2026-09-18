<?php

namespace App\Services\Admin\Bulk;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class UserBulkActionHandler implements AdminBulkActionHandler
{
    public function __construct(private readonly UserService $users) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        if (! in_array($action, ['activate', 'deactivate'], true)) {
            throw ValidationException::withMessages([
                'action' => 'Thao tác tài khoản không hợp lệ.',
            ]);
        }

        $records = User::query()->whereKey($ids)->get();

        if ($records->count() !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'ids' => 'Một hoặc nhiều tài khoản không còn khả dụng.',
            ]);
        }

        $active = $action === 'activate';

        foreach ($records as $user) {
            $this->users->setActive($user, $active);
        }

        return $active
            ? 'Đã kích hoạt các tài khoản được chọn.'
            : 'Đã ngừng kích hoạt các tài khoản được chọn.';
    }
}
