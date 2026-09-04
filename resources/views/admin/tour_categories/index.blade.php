@extends('layouts.admin')

@section('title', 'Loại hình tour')
@section('page-title', 'Loại hình tour')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Loại hình tour</li></ol>
@endsection
@section('content')
<x-admin.index-card title="Danh sách loại hình tour" description="Phân loại theo mục đích và cách trải nghiệm, không trùng với điểm đến." icon="bi-collection" :create-url="route('admin.tour-categories.create')" create-label="Thêm loại hình" resource="tour_category" :order-start="$categories->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.tour-categories.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5"><label class="form-label" for="tour-category-search">Từ khóa</label><input id="tour-category-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên loại hình hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="tour-category-status">Trạng thái</label><select id="tour-category-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2"><label class="form-label" for="tour-category-home">Trang chủ</label><select id="tour-category-home" name="home" class="form-select"><option value="">Tất cả</option><option value="yes" @selected(request('home') === 'yes')>Đang hiển thị</option><option value="no" @selected(request('home') === 'no')>Không hiển thị</option></select></div>
            <div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Lọc</button><a href="{{ route('admin.tour-categories.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên loại hình</th><th>Loại hình cha</th><th class="text-center">Trang chủ</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($categories as $category)
            <tr data-record-id="{{ $category->id }}"><td data-select-column class="text-center"><input form="admin-bulk-tour_category-form" type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $category->name }}"></td><td><a href="{{ route('admin.tour-categories.edit', $category) }}" class="fw-semibold text-decoration-none">{{ $category->name }}</a><small class="d-block text-body-secondary">Thứ tự {{ $category->sort_order }}</small></td><td>{{ $category->parent?->name ?? '—' }}</td><td class="text-center"><x-toggle model="tour_category" :id="$category->id" field="is_home" :checked="$category->is_home" /></td><td class="text-center"><x-toggle model="tour_category" :id="$category->id" field="is_active" :checked="$category->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.tour-categories.edit', $category) }}" class="btn btn-default"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-tour-category-{{ $category->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="6" class="text-center py-5">Chưa có loại hình tour.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($categories as $category)<form id="delete-tour-category-{{ $category->id }}" action="{{ route('admin.tour-categories.destroy', $category) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa loại hình này?" data-delete-warning="Loại hình tour đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($categories->hasPages()){{ $categories->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
