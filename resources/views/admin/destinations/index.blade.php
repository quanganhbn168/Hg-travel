@extends('layouts.admin')

@section('title', 'Điểm đến')
@section('page-title', 'Điểm đến')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Điểm đến</li></ol>
@endsection
@section('content')
<x-admin.index-card title="Danh sách điểm đến" description="Quản lý địa danh, khu vực và điểm đến nổi bật." icon="bi-geo-alt" :create-url="route('admin.destinations.create')" create-label="Thêm điểm đến" resource="destination" :order-start="$destinations->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.destinations.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-7"><label class="form-label" for="destination-search">Từ khóa</label><input id="destination-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên điểm đến hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="destination-status">Trạng thái</label><select id="destination-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Lọc</button><a href="{{ route('admin.destinations.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên điểm đến</th><th>Slug</th><th>Điểm đến cha</th><th class="text-center">Nổi bật</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($destinations as $destination)
            <tr data-record-id="{{ $destination->id }}"><td data-select-column class="text-center"><input form="admin-bulk-destination-form" type="checkbox" name="ids[]" value="{{ $destination->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $destination->name }}"></td><td><a href="{{ route('admin.destinations.edit', $destination) }}" class="fw-semibold text-decoration-none">{{ $destination->name }}</a><small class="d-block text-body-secondary">Thứ tự {{ $destination->sort_order }}</small></td><td><code>{{ $destination->slug }}</code></td><td>{{ $destination->parent?->name ?? '—' }}</td><td class="text-center">@if($destination->is_featured)<i class="bi bi-star-fill text-warning"></i>@else—@endif</td><td class="text-center"><span class="badge text-bg-{{ $destination->is_active ? 'success' : 'secondary' }}">{{ $destination->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.destinations.edit', $destination) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-destination-{{ $destination->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="8" class="text-center py-5">Chưa có điểm đến.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($destinations as $destination)<form id="delete-destination-{{ $destination->id }}" action="{{ route('admin.destinations.destroy', $destination) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa điểm đến này?" data-delete-warning="Điểm đến đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($destinations->hasPages()){{ $destinations->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
