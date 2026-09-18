@extends('layouts.admin')

@section('title', 'Danh mục bài viết')
@section('page-title', 'Danh mục bài viết')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Danh mục bài viết</li>
    </ol>
@endsection

@section('content')
    @php
        $filtersActive = request()->hasAny(['search', 'per_page']);
        $defaultView = $filtersActive ? 'list' : 'tree';
    @endphp

    <div data-index-view-manager data-default-view="{{ $defaultView }}">
        <x-admin.index-card
            title="Quản trị danh mục bài viết"
            description="Xem quan hệ cha/con bằng cây; dùng danh sách khi cần tìm kiếm hoặc thao tác hàng loạt."
            :create-url="route('admin.post-categories.create')"
            create-label="Thêm danh mục"
            resource="post_category"
        >
            <x-slot:filters>
                <form
                    action="{{ route('admin.post-categories.index') }}"
                    method="GET"
                    class="row g-3 align-items-end"
                    data-index-list-filter
                >
                    <div class="col-lg-7">
                        <label class="form-label" for="post-category-search">Từ khóa</label>
                        <input
                            id="post-category-search"
                            class="form-control"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Tên hoặc slug danh mục"
                        >
                    </div>

                    <div class="col-lg-2">
                        <x-admin.per-page />
                    </div>

                    <div class="col-lg-3">
                        <x-admin.filter-actions :reset-url="route('admin.post-categories.index')" />
                    </div>
                </form>
            </x-slot:filters>

            <x-slot:actions>
                <div class="btn-group btn-group-sm" role="group" aria-label="Chế độ xem danh mục bài viết">
                    <button type="button" class="btn btn-primary" data-index-view="tree">
                        <i class="bi bi-diagram-3 me-1"></i>
                        Cây
                    </button>
                    <button type="button" class="btn btn-default" data-index-view="list">
                        <i class="bi bi-list-ul me-1"></i>
                        Danh sách
                    </button>
                </div>
            </x-slot:actions>

            <div data-index-tree-panel>
                <div class="admin-tree-toolbar">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                        <input
                            type="search"
                            class="form-control"
                            placeholder="Tìm danh mục bài viết..."
                            data-tree-search
                        >
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-default btn-sm" data-tree-expand-all>
                            Mở tất cả
                        </button>
                        <button type="button" class="btn btn-default btn-sm" data-tree-collapse-all>
                            Thu gọn
                        </button>
                    </div>
                </div>

                <div class="admin-tree" data-admin-tree>
                    @forelse($categoryTree as $row)
                        @php
                            /** @var AppModelsPostCategory $category */
                            $category = $row['item'];
                        @endphp

                        <x-admin.tree-row
                            :id="$category->id"
                            :parent-id="$row['parent_id']"
                            :depth="$row['depth']"
                            :has-children="$category->children_count > 0"
                            :expanded="$row['depth'] === 0"
                            :search="$row['path'].' '.$category->slug"
                        >
                            <div class="admin-tree-node__main">
                                <div>
                                    <a
                                        href="{{ route('admin.post-categories.edit', $category) }}"
                                        class="fw-semibold text-decoration-none d-block"
                                    >
                                        {{ $category->name }}
                                    </a>
                                    <small class="text-body-secondary">{{ $category->slug }}</small>
                                </div>
                            </div>

                            <div class="admin-tree-node__meta">
                                <span class="text-body-secondary small">
                                    {{ $category->children_count }} mục con
                                </span>
                                <span class="text-body-secondary small">
                                    {{ $category->posts_count }} bài viết
                                </span>
                            </div>

                            <div class="admin-tree-node__status">
                                <x-toggle
                                    model="post_category"
                                    :id="$category->id"
                                    field="is_active"
                                    :checked="$category->is_active"
                                />
                            </div>

                            <div class="admin-tree-node__actions">
                                <x-admin.row-actions
                                    resource="post_category"
                                    :edit-url="route('admin.post-categories.edit', $category)"
                                    :delete-url="route('admin.post-categories.destroy', $category)"
                                    delete-title="Xóa danh mục này?"
                                    :allow-delete="$category->children_count === 0 && $category->posts_count === 0"
                                />
                            </div>
                        </x-admin.tree-row>
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            Chưa có danh mục bài viết.
                        </div>
                    @endforelse
                </div>
            </div>

            <div data-index-list-panel hidden>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <x-admin.select-all resource="post_category" />
                                <th>Tên</th>
                                <th>Danh mục cha</th>
                                <th class="text-center">Bài viết</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr data-record-id="{{ $category->id }}">
                                    <x-admin.select-item
                                        resource="post_category"
                                        :id="$category->id"
                                        :label="$category->name"
                                    />

                                    <td>
                                        <a
                                            class="fw-semibold text-decoration-none"
                                            href="{{ route('admin.post-categories.edit', $category) }}"
                                        >
                                            {{ $category->name }}
                                        </a>
                                        <small class="d-block text-body-secondary">{{ $category->slug }}</small>
                                    </td>

                                    <td>{{ $category->parent?->name ?: '—' }}</td>
                                    <td class="text-center">{{ $category->posts_count }}</td>

                                    <td class="text-center">
                                        <x-toggle
                                            model="post_category"
                                            :id="$category->id"
                                            field="is_active"
                                            :checked="$category->is_active"
                                        />
                                    </td>

                                    <td class="text-end">
                                        <x-admin.row-actions
                                            resource="post_category"
                                            :edit-url="route('admin.post-categories.edit', $category)"
                                            :delete-url="route('admin.post-categories.destroy', $category)"
                                            delete-title="Xóa danh mục này?"
                                            :allow-delete="$category->children_count === 0 && $category->posts_count === 0"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <x-admin.empty-state message="Chưa có danh mục phù hợp." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($categories->hasPages())
                    <div class="card-footer clearfix">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </x-admin.index-card>
    </div>
@endsection
