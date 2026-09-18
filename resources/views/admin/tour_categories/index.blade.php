@extends('layouts.admin')

@section('title', 'Loại hình tour')
@section('page-title', 'Loại hình tour')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Loại hình tour</li>
    </ol>
@endsection

@section('content')
    @php
        $filtersActive = request()->hasAny(['search', 'status', 'home', 'per_page']);
        $defaultView = $filtersActive ? 'list' : 'tree';
    @endphp

    <div data-index-view-manager data-default-view="{{ $defaultView }}">
        <x-admin.index-card
            title="Quản trị loại hình tour"
            description="Xem cấu trúc loại hình theo cây; dùng danh sách khi cần lọc hoặc thao tác hàng loạt."
            :create-url="route('admin.tour-categories.create')"
            create-label="Thêm loại hình"
            resource="tour_category"
        >
            <x-slot:filters>
                <form
                    action="{{ route('admin.tour-categories.index') }}"
                    method="GET"
                    class="row g-3 align-items-end"
                    data-index-list-filter
                >
                    <div class="col-lg-4">
                        <label class="form-label" for="tour-category-search">Từ khóa</label>
                        <input
                            id="tour-category-search"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Tên loại hình hoặc slug"
                        >
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label" for="tour-category-status">Trạng thái</label>
                        <select id="tour-category-status" name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label" for="tour-category-home">Trang chủ</label>
                        <select id="tour-category-home" name="home" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="yes" @selected(request('home') === 'yes')>Đang hiển thị</option>
                            <option value="no" @selected(request('home') === 'no')>Không hiển thị</option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <x-admin.per-page />
                    </div>

                    <div class="col-lg-2">
                        <x-admin.filter-actions :reset-url="route('admin.tour-categories.index')" />
                    </div>
                </form>
            </x-slot:filters>

            <x-slot:actions>
                <div class="btn-group btn-group-sm" role="group" aria-label="Chế độ xem loại hình tour">
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
                            placeholder="Tìm loại hình tour..."
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
                            /** @var \App\Models\TourCategory $category */
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
                                <x-admin.thumbnail
                                    :src="$category->cover_url"
                                    :alt="$category->name"
                                    :href="route('admin.tour-categories.edit', $category)"
                                />

                                <div>
                                    <a
                                        href="{{ route('admin.tour-categories.edit', $category) }}"
                                        class="fw-semibold text-decoration-none text-truncate d-block"
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
                                    {{ $category->tours_count }} tour
                                </span>
                            </div>

                            <div class="admin-tree-node__status">
                                <x-toggle
                                    model="tour_category"
                                    :id="$category->id"
                                    field="is_active"
                                    :checked="$category->is_active"
                                />
                            </div>

                            <div class="admin-tree-node__actions">
                                <x-admin.row-actions
                                    resource="tour_category"
                                    :edit-url="route('admin.tour-categories.edit', $category)"
                                    :delete-url="route('admin.tour-categories.destroy', $category)"
                                    delete-title="Xóa loại hình này?"
                                    :allow-delete="$category->children_count === 0 && $category->tours_count === 0"
                                />
                            </div>
                        </x-admin.tree-row>
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            Chưa có loại hình tour.
                        </div>
                    @endforelse
                </div>
            </div>

            <div data-index-list-panel hidden>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <x-admin.select-all resource="tour_category" />
                                <th style="width:76px">Ảnh</th>
                                <th>Tên loại hình</th>
                                <th>Loại hình cha</th>
                                <th class="text-center">Tour</th>
                                <th class="text-center">Trang chủ</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr data-record-id="{{ $category->id }}">
                                    <x-admin.select-item
                                        resource="tour_category"
                                        :id="$category->id"
                                        :label="$category->name"
                                    />

                                    <td>
                                        <x-admin.thumbnail
                                            :src="$category->cover_url"
                                            :alt="$category->name"
                                            :href="route('admin.tour-categories.edit', $category)"
                                            :size="56"
                                        />
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route('admin.tour-categories.edit', $category) }}"
                                            class="fw-semibold text-decoration-none"
                                        >
                                            {{ $category->name }}
                                        </a>
                                        <small class="d-block text-body-secondary">
                                            {{ $category->slug }}
                                        </small>
                                    </td>

                                    <td>{{ $category->parent?->name ?? '—' }}</td>
                                    <td class="text-center">{{ $category->tours_count }}</td>

                                    <td class="text-center">
                                        <x-toggle
                                            model="tour_category"
                                            :id="$category->id"
                                            field="is_home"
                                            :checked="$category->is_home"
                                        />
                                    </td>

                                    <td class="text-center">
                                        <x-toggle
                                            model="tour_category"
                                            :id="$category->id"
                                            field="is_active"
                                            :checked="$category->is_active"
                                        />
                                    </td>

                                    <td class="text-end">
                                        <x-admin.row-actions
                                            resource="tour_category"
                                            :edit-url="route('admin.tour-categories.edit', $category)"
                                            :delete-url="route('admin.tour-categories.destroy', $category)"
                                            delete-title="Xóa loại hình này?"
                                            :allow-delete="$category->children_count === 0 && $category->tours_count === 0"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <x-admin.empty-state message="Chưa có loại hình tour phù hợp." />
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
