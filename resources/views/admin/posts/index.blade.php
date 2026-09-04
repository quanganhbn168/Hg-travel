@extends('layouts.admin')

@section('title', 'Bài viết')
@section('page-title', 'Bài viết')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Bài viết</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách bài viết" description="Quản lý tin tức, cẩm nang và nội dung SEO." icon="bi-newspaper" :create-url="route('admin.posts.create')" create-label="Thêm bài viết" resource="post">
    <x-slot:filters>
        <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label" for="post-search">Từ khóa</label><input id="post-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tìm bài viết"></div>
            <div class="col-lg-4"><label class="form-label" for="post-status">Trạng thái</label><select id="post-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Hiển thị</option><option value="inactive" @selected(request('status') === 'inactive')>Ẩn</option></select></div>
            <div class="col-lg-2"><button class="btn btn-primary w-100" type="submit">Lọc</button></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Bài viết</th><th>Danh mục</th><th>Tác giả</th><th class="text-center">Hiển thị</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($posts as $post)
            <tr data-record-id="{{ $post->id }}"><td data-select-column class="text-center"><input form="admin-bulk-post-form" type="checkbox" name="ids[]" value="{{ $post->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $post->name }}"></td><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.posts.edit', $post) }}">{{ $post->name }}</a><small class="d-block text-muted">{{ $post->slug }}</small></td><td>{{ $post->category?->name ?: '—' }}</td><td>{{ $post->author?->name ?: '—' }}</td><td class="text-center"><x-toggle model="post" :id="$post->id" field="is_active" :checked="$post->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-default"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-post-{{ $post->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có bài viết.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($posts as $post)<form id="delete-post-{{ $post->id }}" action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa bài viết này?" data-delete-warning="Bài viết sẽ được đưa vào thùng rác.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($posts->hasPages()){{ $posts->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
