@extends('layouts.admin')

@section('title', $menu->exists ? 'Sửa menu' : 'Thêm menu')
@section('page-title', $menu->exists ? 'Sửa menu' : 'Thêm menu')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">Menu website</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $menu->exists ? $menu->name : 'Thêm menu' }}</li>
    </ol>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Chưa thể lưu menu.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $menu->exists ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" data-menu-builder>
        @csrf
        @if ($menu->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="items_present" value="1">
        <input type="hidden" name="items_json" value="[]" data-menu-payload>

        <div class="row g-3">
            <div class="col-xl-4">
                <x-card title="Thêm vào menu" type="primary" :collapsible="true">
                    <div class="menu-source-picker" data-menu-source-picker>
                        <label class="form-label" for="menu-source-search">Tìm nội dung</label>
                        <input id="menu-source-search" type="search" class="form-control mb-3" data-menu-source-search placeholder="Tên tour, dịch vụ, bài viết...">

                        <div class="menu-source-groups">
                            @foreach ($sourceGroups as $group)
                                <details class="menu-source-group" data-menu-source-group @if ($loop->first) open @endif>
                                    <summary>
                                        <span><i class="bi {{ $group['icon'] }} me-2 text-primary" aria-hidden="true"></i>{{ $group['label'] }}</span>
                                        <span class="badge text-bg-light">{{ count($group['items']) }}</span>
                                    </summary>
                                    <div class="menu-source-group__items">
                                        @foreach ($group['items'] as $source)
                                            <button
                                                type="button"
                                                class="menu-source-item"
                                                data-menu-source-button
                                                data-menu-source="{{ base64_encode(json_encode($source, JSON_UNESCAPED_UNICODE)) }}"
                                                data-menu-search="{{ mb_strtolower($source['label'].' '.$source['meta']) }}"
                                                title="Thêm vào menu"
                                            >
                                                <span class="min-w-0">
                                                    <strong class="d-block text-truncate">{{ $source['label'] }}</strong>
                                                    <small class="text-body-secondary">{{ $source['meta'] }}</small>
                                                </span>
                                                <i class="bi bi-plus-lg text-primary" aria-hidden="true"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        <div class="menu-custom-source border-top mt-3 pt-3">
                            <div class="small fw-semibold mb-2">Liên kết tuỳ chỉnh</div>
                            <input type="text" class="form-control mb-2" data-menu-custom-label placeholder="Nhãn hiển thị">
                            <input type="url" class="form-control mb-2" data-menu-custom-url placeholder="https://... hoặc /duong-dan">
                            <select class="form-select mb-2" data-menu-custom-target>
                                <option value="_self">Cùng tab</option>
                                <option value="_blank">Tab mới</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary w-100" data-menu-custom-add>
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Thêm link custom
                            </button>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="col-xl-8">
                <x-card title="Thông tin menu" type="primary">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-input name="name" label="Tên menu" :value="$menu->name" required />
                        </div>
                        <div class="col-md-6">
                            <x-select name="location" label="Vị trí hiển thị" :options="['header' => 'Header', 'footer' => 'Footer']" :selected="$menu->location" placeholder="Chọn vị trí" :tom-select="false" required />
                        </div>
                        <div class="col-12">
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($menu->is_active)>
                                <span class="form-check-label">Kích hoạt menu</span>
                            </label>
                            <div class="form-text">Header và footer mỗi vị trí chỉ nên có một menu đang bật.</div>
                        </div>
                    </div>
                </x-card>

                <x-card title="Cấu trúc menu" type="primary" class="mt-3">
                    <x-slot:header>
                        <span class="badge text-bg-light">Kéo thả để sắp xếp</span>
                    </x-slot:header>
                    <p class="text-body-secondary small mb-3">Chọn nội dung ở bên trái để thêm vào cuối danh sách. Mở từng mục để đổi nhãn, target hoặc trạng thái; kéo mục vào mục khác để tạo cấp con.</p>

                    <ul class="menu-builder__list menu-builder__root list-unstyled mb-0" data-menu-list>
                        @forelse ($menuItems as $item)
                            @include('admin.menus._item', ['item' => $item])
                        @empty
                            <li class="menu-builder__empty" data-menu-empty>Chưa có mục menu. Hãy chọn nội dung ở bên trái.</li>
                        @endforelse
                    </ul>
                </x-card>

                <div class="d-flex justify-content-between gap-2 mt-3">
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ $menu->exists ? 'Lưu thay đổi' : 'Tạo menu' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <template data-menu-item-template>
        @include('admin.menus._item', ['item' => [
            'id' => null,
            'title' => 'Mục menu mới',
            'url' => '',
            'route_name' => '',
            'target' => '_self',
            'is_active' => true,
            'linked_source_id' => null,
            'linked_source_type' => 'custom',
            'link_type_label' => 'Liên kết custom',
            'link_summary' => 'Chưa có liên kết',
            'children' => [],
        ]])
    </template>
@endsection
