@extends('layouts.admin')

@section('title', 'Bài viết')
@section('page-title', 'Bài viết')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Bài viết</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách bài viết"
    description="Quản lý tin tức, cẩm nang và nội dung SEO."
    :create-url="route('admin.posts.create')"
    create-label="Thêm bài viết"
    resource="post"
>
    <x-slot:filters>
        <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label" for="post-search">Từ khóa</label>
                <input id="post-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên hoặc slug bài viết">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="post-category">Danh mục</label>
                <select id="post-category" name="category" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="post-status">Trạng thái</label>
                <select id="post-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Hiển thị</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ẩn</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-2">
                <x-admin.filter-actions :reset-url="route('admin.posts.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="post" />
                    <th style="width:76px">Ảnh</th>
                    <th>Bài viết</th>
                    <th>Danh mục</th>
                    <th>Tác giả</th>
                    <th class="text-center">Hiển thị</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr data-record-id="{{ $post->id }}">
                        <x-admin.select-item resource="post" :id="$post->id" :label="$post->name" />

                        <td>
                            <x-admin.thumbnail
                                :src="$post->cover_url"
                                :alt="$post->name"
                                :href="route('admin.posts.edit', $post)"
                                :size="56"
                            />
                        </td>

                        <td>
                            <a class="fw-semibold text-decoration-none" href="{{ route('admin.posts.edit', $post) }}">
                                {{ $post->name }}
                            </a>
                            <small class="d-block text-body-secondary">{{ $post->slug }}</small>
                        </td>

                        <td>{{ $post->category?->name ?: '—' }}</td>
                        <td>{{ $post->author?->name ?: '—' }}</td>

                        <td class="text-center">
                            <x-toggle model="post" :id="$post->id" field="is_active" :checked="$post->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="post"
                                :view-url="route('posts.show', $post)"
                                :edit-url="route('admin.posts.edit', $post)"
                                :delete-url="route('admin.posts.destroy', $post)"
                                delete-title="Xóa bài viết này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có bài viết." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($posts->hasPages())
            {{ $posts->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
