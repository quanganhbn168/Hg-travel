@extends('layouts.admin')

@section('title', 'Phân quyền')
@section('page-title', 'Phân quyền')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Phân quyền</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách vai trò"
    description="Tạo vai trò và giới hạn quyền truy cập cho từng nhóm tài khoản."
    :create-url="route('admin.roles.create')"
    create-label="Thêm vai trò"
    resource="role"
>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Vai trò</th>
                    <th class="text-center">Tài khoản</th>
                    <th class="text-center">Quyền</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr data-record-id="{{ $role->id }}">
                        <td>
                            <div class="fw-semibold">{{ $role->name }}</div>
                            @if($role->name === 'admin')
                                <span class="badge text-bg-primary">Toàn quyền hệ thống</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $role->users_count }}</td>
                        <td class="text-center">{{ $role->permissions_count }}</td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="role"
                                :edit-url="route('admin.roles.edit', $role)"
                                :delete-url="$role->name === 'admin' ? null : route('admin.roles.destroy', $role)"
                                edit-title="Sửa vai trò"
                                delete-title="Xóa vai trò này?"
                                :allow-delete="$role->name !== 'admin' && $role->users_count === 0"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có vai trò." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($roles->hasPages())
            {{ $roles->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
