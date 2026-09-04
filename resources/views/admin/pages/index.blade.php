@extends('layouts.admin')

@section('title', 'Trang tĩnh')
@section('page-title', 'Trang tĩnh')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Trang tĩnh</li></ol>
@endsection
@section('content')
<x-admin.index-card title="Danh sách trang" description="Quản lý các trang nội dung và SEO." icon="bi-file-earmark-text" :create-url="route('admin.pages.create')" create-label="Thêm trang" resource="page" :order-start="$pages->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.pages.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label" for="page-search">Từ khóa</label><input id="page-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm tên hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="page-status">Trạng thái</label><select id="page-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hiển thị</option><option value="inactive" @selected(request('status') === 'inactive')>Đã tắt</option></select></div>
            <div class="col-lg-2"><label class="form-label" for="page-per-page">Số dòng</label><select id="page-per-page" name="per_page" class="form-select">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Lọc</button><a href="{{ route('admin.pages.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên trang</th><th>Slug</th><th>Template</th><th class="text-center">Hiển thị</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($pages as $page)
            <tr data-record-id="{{ $page->id }}"><td data-select-column class="text-center"><input form="admin-bulk-page-form" type="checkbox" name="ids[]" value="{{ $page->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $page->name }}"></td><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.pages.edit', $page) }}">{{ $page->name }}</a><small class="d-block text-body-secondary">Thứ tự {{ $page->sort_order }}</small></td><td><code>{{ $page->slug }}</code></td><td>{{ $page->template }}</td><td class="text-center"><x-toggle model="page" :id="$page->id" field="is_active" :checked="$page->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-default"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-page-{{ $page->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có trang tĩnh.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($pages as $page)<form id="delete-page-{{ $page->id }}" action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa trang này?" data-delete-warning="Trang sẽ được đưa vào thùng rác.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($pages->hasPages()){{ $pages->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
