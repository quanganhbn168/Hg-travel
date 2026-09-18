<?php

namespace App\Services;

use App\Models\User;
use App\Support\AdminIndexRegistry;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class AdminToggleService
{
    public function execute(string $resource, int $id, string $field, bool $value): array
    {
        $modelClass = AdminIndexRegistry::modelFor($resource);

        if (! $modelClass || ! in_array($field, AdminIndexRegistry::toggleFieldsFor($resource), true)) {
            abort(422, 'Trường bật/tắt không hợp lệ.');
        }

        /** @var Model $model */
        $model = $modelClass::query()->findOrFail($id);

        if ($resource === 'destination' && (bool) $model->getAttribute('is_system')) {
            throw new AuthorizationException('Nhóm địa lý hệ thống không được thay đổi.');
        }

        if ($resource === 'user' && $field === 'is_active' && $model instanceof User) {
            app(UserService::class)->setActive($model, $value);
        } else {
            $model->update([$field => $value]);
        }

        return [
            'value' => (bool) $model->fresh()->getAttribute($field),
            'message' => sprintf(
                'Đã %s %s của %s.',
                $value ? 'bật' : 'tắt',
                $this->fieldLabel($field),
                AdminIndexRegistry::labelFor($resource),
            ),
        ];
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'is_home' => 'hiển thị trang chủ',
            'is_featured' => 'đánh dấu nổi bật',
            default => 'trạng thái',
        };
    }
}
