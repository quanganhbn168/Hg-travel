@extends('layouts.admin')

@section('title', 'Điểm đến')
@section('page-title', 'Điểm đến')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Điểm đến</li></ol>
@endsection

@section('content')
    <x-admin.index-header description="Quản lý địa danh, khu vực và điểm đến nổi bật." :create-url="route('admin.destinations.create')" create-label="Thêm điểm đến" />
    <x-admin.filter-panel title="Bộ lọc điểm đến">
        <form action="{{ route('admin.destinations.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-7"><label class="form-label" for="destination-search">Từ khóa</label><input id="destination-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên điểm đến hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="destination-status">Trạng thái</label><select id="destination-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Lọc</button><a href="{{ route('admin.destinations.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-admin.filter-panel>
    <x-admin.table-card title="Danh sách điểm đến">
        <x-slot:tools><span class="badge text-bg-light">{{ $destinations->total() }} bản ghi</span></x-slot:tools>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>#</th><th>Tên điểm đến</th><th>Slug</th><th>Điểm đến cha</th><th class="text-center">Nổi bật</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($destinations as $destination)<tr><td class="text-body-secondary">#{{ $destination->id }}</td><td><a href="{{ route('admin.destinations.edit', $destination) }}" class="fw-semibold text-decoration-none">{{ $destination->name }}</a><small class="d-block text-body-secondary">Thứ tự {{ $destination->sort_order }}</small></td><td><code>{{ $destination->slug }}</code></td><td>{{ $destination->parent?->name ?? '—' }}</td><td class="text-center">@if($destination->is_featured)<i class="bi bi-star-fill text-warning"></i>@else—@endif</td><td class="text-center"><span class="badge text-bg-{{ $destination->is_active ? 'success' : 'secondary' }}">{{ $destination->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><a href="{{ route('admin.destinations.edit', $destination) }}" class="btn btn-default btn-sm" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a><button form="delete-destination-{{ $destination->id }}" class="btn btn-default btn-sm text-danger" title="Xóa"><i class="bi bi-trash"></i></button></td></tr>@empty<tr><td colspan="7" class="text-center py-5">Chưa có điểm đến.</td></tr>@endforelse</tbody></table></div>
        @foreach($destinations as $destination)<form id="delete-destination-{{ $destination->id }}" action="{{ route('admin.destinations.destroy', $destination) }}" method="POST" class="d-none" onsubmit="return confirm('Xóa điểm đến này?')">@csrf @method('DELETE')</form>@endforeach
        <x-slot:footer>@if($destinations->hasPages()){{ $destinations->links() }}@endif</x-slot:footer>
    </x-admin.table-card>
@endsection
