@extends('layouts.admin')

@section('title', 'Danh mục dịch vụ')
@section('page-title', 'Danh mục dịch vụ')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Danh mục dịch vụ</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách danh mục dịch vụ" description="Quản lý các nhóm dịch vụ hiển thị trên catalogue và landing page." icon="bi-layers" :create-url="route('admin.service-categories.create')" create-label="Thêm danh mục" resource="service_category" :order-start="$categories->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.service-categories.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5"><label class="form-label" for="service-category-search">Từ khóa</label><input id="service-category-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="service-category-status">Trạng thái</label><select id="service-category-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2"></div><div class="col-lg-2"><button class="btn btn-primary w-100" type="submit">Lọc</button></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Danh mục</th><th>Slug</th><th class="text-center">Dịch vụ</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($categories as $category)
            <tr data-record-id="{{ $category->id }}"><td data-select-column class="text-center"><input form="admin-bulk-service_category-form" type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $category->name }}"></td><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.service-categories.edit', $category) }}">{{ $category->name }}</a><small class="d-block text-body-secondary">{{ $category->kicker ?: 'Không có kicker' }} · Thứ tự {{ $category->sort_order }}</small></td><td><code>{{ $category->slug }}</code></td><td class="text-center">{{ $category->services_count }}</td><td class="text-center"><span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.service-categories.edit', $category) }}" class="btn btn-default"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-service-category-{{ $category->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có danh mục dịch vụ.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($categories as $category)<form id="delete-service-category-{{ $category->id }}" action="{{ route('admin.service-categories.destroy', $category) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa danh mục này?" data-delete-warning="Chỉ xóa được danh mục chưa có dịch vụ liên kết.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($categories->hasPages()){{ $categories->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
