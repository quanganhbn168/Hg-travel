@extends('layouts.admin')

@section('title', 'Menu website')
@section('page-title', 'Menu website')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Menu website</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách menu"
    description="Quản lý điều hướng header và footer. Mỗi vị trí chỉ có một menu."
    :create-url="route('admin.menus.create')"
    create-label="Thêm menu"
    resource="menu"
>
    <x-slot:filters>
        <form action="{{ route('admin.menus.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="menu-search">Từ khóa</label>
                <input id="menu-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc vị trí menu">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="menu-status">Trạng thái</label>
                <select id="menu-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.menus.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="menu" />
                    <th>Tên</th>
                    <th>Vị trí</th>
                    <th class="text-center">Số mục</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                    <tr data-record-id="{{ $menu->id }}">
                        <x-admin.select-item resource="menu" :id="$menu->id" :label="$menu->name" />

                        <td class="fw-semibold">{{ $menu->name }}</td>
                        <td><code>{{ $menu->location }}</code></td>
                        <td class="text-center">{{ $menu->items_count }}</td>

                        <td class="text-center">
                            <x-toggle model="menu" :id="$menu->id" field="is_active" :checked="$menu->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="menu"
                                :edit-url="route('admin.menus.edit', $menu)"
                                :delete-url="route('admin.menus.destroy', $menu)"
                                delete-title="Xóa menu này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có menu." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($menus->hasPages())
            {{ $menus->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
