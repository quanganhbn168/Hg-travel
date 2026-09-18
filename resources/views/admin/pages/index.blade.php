@extends('layouts.admin')

@section('title', 'Trang tĩnh')
@section('page-title', 'Trang tĩnh')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Trang tĩnh</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách trang"
    description="Quản lý các trang nội dung và SEO."
    :create-url="route('admin.pages.create')"
    create-label="Thêm trang"
    resource="page"
    :order-start="$pages->firstItem() ?? 1"
>
    <x-slot:filters>
        <form action="{{ route('admin.pages.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="page-search">Từ khóa</label>
                <input id="page-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm tên hoặc slug">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="page-status">Trạng thái</label>
                <select id="page-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hiển thị</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đã tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.pages.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="page" />
                    <th>Tên trang</th>
                    <th>Slug</th>
                    <th>Template</th>
                    <th class="text-center">Hiển thị</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    <tr data-record-id="{{ $page->id }}">
                        <x-admin.select-item resource="page" :id="$page->id" :label="$page->name" />

                        <td>
                            <a class="fw-semibold text-decoration-none" href="{{ route('admin.pages.edit', $page) }}">
                                {{ $page->name }}
                            </a>
                            <small class="d-block text-body-secondary">Thứ tự {{ $page->sort_order }}</small>
                        </td>

                        <td><code>{{ $page->slug }}</code></td>
                        <td>{{ $page->template }}</td>

                        <td class="text-center">
                            <x-toggle model="page" :id="$page->id" field="is_active" :checked="$page->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="page"
                                :view-url="route('pages.show', ['page' => $page->slug])"
                                :edit-url="route('admin.pages.edit', $page)"
                                :delete-url="route('admin.pages.destroy', $page)"
                                delete-title="Xóa trang này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có trang tĩnh." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($pages->hasPages())
            {{ $pages->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
