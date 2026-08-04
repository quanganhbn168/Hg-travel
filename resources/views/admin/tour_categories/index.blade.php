@extends('layouts.admin')

@section('title', 'Danh mục tour')
@section('page-title', 'Danh mục tour')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Danh mục tour</li></ol>
@endsection

@section('content')
    <x-admin.index-header description="Tổ chức các nhóm chương trình du lịch." :create-url="route('admin.tour-categories.create')" create-label="Thêm danh mục" />
    <x-admin.filter-panel title="Bộ lọc danh mục">
        <form action="{{ route('admin.tour-categories.index') }}" class="row g-3 align-items-end"><div class="col-lg-5"><label class="form-label" for="tour-category-search">Từ khóa</label><input id="tour-category-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên danh mục hoặc slug"></div><div class="col-lg-3"><label class="form-label" for="tour-category-status">Trạng thái</label><select id="tour-category-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div><div class="col-lg-2"><label class="form-label" for="tour-category-home">Trang chủ</label><select id="tour-category-home" name="home" class="form-select"><option value="">Tất cả</option><option value="yes" @selected(request('home') === 'yes')>Đang hiển thị</option><option value="no" @selected(request('home') === 'no')>Không hiển thị</option></select></div><div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Lọc</button><a href="{{ route('admin.tour-categories.index') }}" class="btn btn-default"><i class="bi bi-arrow-counterclockwise"></i></a></div></form>
    </x-admin.filter-panel>
    <x-admin.table-card title="Danh sách danh mục">
        <x-slot:tools><span class="badge text-bg-light">{{ $categories->total() }} bản ghi</span></x-slot:tools>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>#</th><th>Tên danh mục</th><th>Slug</th><th>Danh mục cha</th><th class="text-center">Trang chủ</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($categories as $category)<tr><td class="text-body-secondary">#{{ $category->id }}</td><td><a href="{{ route('admin.tour-categories.edit', $category) }}" class="fw-semibold text-decoration-none">{{ $category->name }}</a><small class="d-block text-body-secondary">Thứ tự {{ $category->sort_order }}</small></td><td><code>{{ $category->slug }}</code></td><td>{{ $category->parent?->name ?? '—' }}</td><td class="text-center"><span class="badge text-bg-{{ $category->is_home ? 'primary' : 'light' }}">{{ $category->is_home ? 'Hiển thị' : 'Không' }}</span></td><td class="text-center"><span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><a href="{{ route('admin.tour-categories.edit', $category) }}" class="btn btn-default btn-sm"><i class="bi bi-pencil-square"></i></a><button form="delete-tour-category-{{ $category->id }}" class="btn btn-default btn-sm text-danger"><i class="bi bi-trash"></i></button></td></tr>@empty<tr><td colspan="7" class="text-center py-5">Chưa có danh mục.</td></tr>@endforelse</tbody></table></div>
        @foreach($categories as $category)<form id="delete-tour-category-{{ $category->id }}" action="{{ route('admin.tour-categories.destroy', $category) }}" method="POST" class="d-none" onsubmit="return confirm('Xóa danh mục này?')">@csrf @method('DELETE')</form>@endforeach
        <x-slot:footer>@if($categories->hasPages()){{ $categories->links() }}@endif</x-slot:footer>
    </x-admin.table-card>
@endsection
