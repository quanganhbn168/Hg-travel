<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $paginator = User::with('roles')
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', '%'.trim($filters['search']).'%')
                        ->orWhere('email', 'like', '%'.trim($filters['search']).'%')
                )
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $activeAdminCount = User::query()
            ->where('is_active', true)
            ->whereHas(
                'roles',
                fn ($query) => $query
                    ->where('name', 'admin')
                    ->where('guard_name', 'web')
            )
            ->count();

        $currentAdminId = (int) auth('admin')->id();

        $paginator->getCollection()->each(
            function (User $user) use ($activeAdminCount, $currentAdminId): void {
                $isSelf = $currentAdminId > 0
                    && $currentAdminId === (int) $user->getKey();

                $isLastActiveAdmin = (bool) $user->is_active
                    && $user->roles->contains('name', 'admin')
                    && $activeAdminCount <= 1;

                $user->setAttribute(
                    'access_protected',
                    $isSelf || $isLastActiveAdmin,
                );
            }
        );

        return $paginator;
    }

    public function formContext(?User $user = null): array
    {
        return [
            'user' => $user ?: new User(['is_active' => true]),
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->pluck('name', 'name'),
        ];
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function update(User $user, array $data): void
    {
        $nextActive = (bool) ($data['is_active'] ?? false);
        $nextRole = (string) $data['role'];

        $this->ensureAccessChangeIsSafe($user, $nextActive, $nextRole);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $nextActive,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles([$nextRole]);
    }

    public function setActive(User $user, bool $active): void
    {
        $currentRole = (string) ($user->roles()->value('name') ?? '');

        $this->ensureAccessChangeIsSafe($user, $active, $currentRole);

        $user->update(['is_active' => $active]);
    }

    private function ensureAccessChangeIsSafe(User $user, bool $nextActive, string $nextRole): void
    {
        $currentAdminId = (int) auth('admin')->id();
        $isSelf = $currentAdminId > 0 && $currentAdminId === (int) $user->getKey();

        if ($isSelf && ! $nextActive) {
            throw ValidationException::withMessages([
                'is_active' => 'Bạn không thể tự vô hiệu hóa tài khoản đang đăng nhập.',
            ]);
        }

        $currentlyActiveAdmin = (bool) $user->is_active
            && $user->roles()->where('name', 'admin')->where('guard_name', 'web')->exists();
        $willRemainActiveAdmin = $nextActive && $nextRole === 'admin';

        if ($currentlyActiveAdmin && ! $willRemainActiveAdmin && ! $this->hasOtherActiveAdmin($user)) {
            throw ValidationException::withMessages([
                'is_active' => 'Hệ thống phải luôn còn ít nhất một tài khoản admin đang hoạt động.',
                'role' => 'Không thể bỏ vai trò admin khỏi tài khoản admin hoạt động cuối cùng.',
            ]);
        }
    }

    private function hasOtherActiveAdmin(User $user): bool
    {
        return User::query()
            ->whereKeyNot($user->getKey())
            ->where('is_active', true)
            ->whereHas(
                'roles',
                fn ($query) => $query
                    ->where('name', 'admin')
                    ->where('guard_name', 'web')
            )
            ->exists();
    }
}
