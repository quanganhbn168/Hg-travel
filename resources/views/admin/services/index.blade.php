@extends('layouts.admin')

@section('title', 'Dịch vụ')
@section('page-title', 'Dịch vụ')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Dịch vụ</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách dịch vụ" description="Quản lý từng dịch vụ thuộc catalogue, landing page và các giải pháp trọng tâm." icon="bi-briefcase" :create-url="route('admin.services.create')" create-label="Thêm dịch vụ" resource="service" :order-start="$services->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.services.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label" for="service-search">Từ khóa</label><input id="service-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="service-category">Danh mục</label><select id="service-category" name="category" class="form-select"><option value="">Tất cả</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-lg-3"><label class="form-label" for="service-status">Trạng thái</label><select id="service-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2"><button class="btn btn-primary w-100" type="submit">Lọc</button></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Dịch vụ</th><th>Danh mục</th><th>Slug</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($services as $service)
            <tr data-record-id="{{ $service->id }}"><td data-select-column class="text-center"><input form="admin-bulk-service-form" type="checkbox" name="ids[]" value="{{ $service->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $service->name }}"></td><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.services.edit', $service) }}">{{ $service->name }}</a><small class="d-block text-body-secondary">{{ $service->description ? \Illuminate\Support\Str::limit($service->description, 90) : 'Chưa có mô tả' }} · Thứ tự {{ $service->sort_order }}</small></td><td>{{ $service->category?->name ?: '—' }}</td><td><code>{{ $service->slug }}</code></td><td class="text-center"><x-toggle model="service" :id="$service->id" field="is_active" :checked="$service->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('services.show', ['service' => $service->slug]) }}" target="_blank" class="btn btn-default" title="Xem trang"><i class="bi bi-box-arrow-up-right"></i></a><a href="{{ route('admin.services.edit', $service) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-service-{{ $service->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có dịch vụ.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($services as $service)<form id="delete-service-{{ $service->id }}" action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa dịch vụ này?" data-delete-warning="Dịch vụ đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($services->hasPages()){{ $services->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
