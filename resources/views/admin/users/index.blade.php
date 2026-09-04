@extends('layouts.admin')

@section('title', 'Tài khoản admin')
@section('page-title', 'Tài khoản admin')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Tài khoản admin</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách tài khoản" description="Quản lý tài khoản truy cập khu vực quản trị." icon="bi-people" :create-url="route('admin.users.create')" create-label="Thêm tài khoản" resource="user">
    <x-slot:filters>
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-end"><div class="col-lg-8"><label class="form-label" for="user-search">Tìm kiếm</label><input id="user-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email"></div><div class="col-lg-2"><label class="form-label" for="user-per-page">Số dòng</label><select id="user-per-page" name="per_page" class="form-select">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div><div class="col-lg-2"><button class="btn btn-primary w-100" type="submit">Lọc</button></div></form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($users as $user)
            <tr data-record-id="{{ $user->id }}"><td data-select-column class="text-center"><input form="admin-bulk-user-form" type="checkbox" name="ids[]" value="{{ $user->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $user->name }}"></td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->roles->pluck('name')->join(', ') }}</td><td class="text-center"><x-toggle model="user" :id="$user->id" field="is_active" :checked="$user->is_active" /></td><td class="text-end"><a class="btn btn-default btn-sm" href="{{ route('admin.users.edit', $user) }}"><i class="bi bi-pencil-square"></i></a></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có tài khoản.</td></tr>
        @endforelse
    </tbody></table></div>
    <x-slot:footer>@if($users->hasPages()){{ $users->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
