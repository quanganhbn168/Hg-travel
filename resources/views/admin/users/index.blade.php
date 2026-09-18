@extends('layouts.admin')

@section('title', 'Tài khoản admin')
@section('page-title', 'Tài khoản admin')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tài khoản admin</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách tài khoản"
    description="Quản lý tài khoản truy cập khu vực quản trị."
    :create-url="route('admin.users.create')"
    create-label="Thêm tài khoản"
    resource="user"
>
    <x-slot:filters>
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-7">
                <label class="form-label" for="user-search">Tìm kiếm</label>
                <input id="user-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email">
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.users.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="user" />
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr data-record-id="{{ $user->id }}">
                        <x-admin.select-item
                            resource="user"
                            :id="$user->id"
                            :label="$user->name"
                            :disabled="(bool) $user->access_protected"
                        />

                        <td>
                            <span class="fw-semibold">{{ $user->name }}</span>
                            @if((int) auth('admin')->id() === (int) $user->id)
                                <span class="badge text-bg-light border ms-1">Bạn</span>
                            @elseif($user->access_protected)
                                <span class="badge text-bg-light border ms-1">Admin dự phòng</span>
                            @endif
                        </td>

                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>

                        <td class="text-center">
                            <x-toggle
                                model="user"
                                :id="$user->id"
                                field="is_active"
                                :checked="$user->is_active"
                                :disabled="(bool) $user->access_protected"
                            />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="user"
                                :edit-url="route('admin.users.edit', $user)"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có tài khoản." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($users->hasPages())
            {{ $users->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
