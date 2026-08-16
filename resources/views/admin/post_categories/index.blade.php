@extends('layouts.admin')

@section('title', 'Danh mục bài viết')
@section('page-title', 'Danh mục bài viết')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Danh mục bài viết</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách danh mục bài viết" description="Phân loại bài viết tin tức và cẩm nang." icon="bi-tags" :create-url="route('admin.post-categories.create')" create-label="Thêm danh mục" resource="post_category" :order-start="$categories->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.post-categories.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-7"><label class="form-label" for="post-category-search">Từ khóa</label><input id="post-category-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên danh mục"></div>
            <div class="col-lg-3"><label class="form-label" for="post-category-per-page">Số dòng</label><select id="post-category-per-page" name="per_page" class="form-select">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="col-lg-2"><button class="btn btn-primary w-100" type="submit">Lọc</button></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên</th><th>Danh mục cha</th><th>Thứ tự</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($categories as $category)
            <tr data-record-id="{{ $category->id }}"><td data-select-column class="text-center"><input form="admin-bulk-post_category-form" type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $category->name }}"></td><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.post-categories.edit', $category) }}">{{ $category->name }}</a><small class="d-block text-body-secondary">{{ $category->slug }}</small></td><td>{{ $category->parent?->name ?: '—' }}</td><td>{{ $category->sort_order }}</td><td class="text-center"><span class="badge text-bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Bật' : 'Tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.post-categories.edit', $category) }}" class="btn btn-default"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-post-category-{{ $category->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có danh mục.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($categories as $category)<form id="delete-post-category-{{ $category->id }}" action="{{ route('admin.post-categories.destroy', $category) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa danh mục này?" data-delete-warning="Danh mục sẽ được đưa vào thùng rác.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($categories->hasPages()){{ $categories->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
