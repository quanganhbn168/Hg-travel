@extends('layouts.admin')

@section('title', 'Điểm đến')
@section('page-title', 'Điểm đến')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin-destinations.css') }}?v={{ filemtime(public_path('css/admin-destinations.css')) }}">
@endpush

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Điểm đến</li>
    </ol>
@endsection

@section('content')
    @php
        $listFiltersActive = request()->hasAny([
            'search',
            'parent_id',
            'type',
            'market',
            'status',
            'per_page',
        ]);

        $defaultView = $listFiltersActive ? 'list' : 'tree';
        $quickValidation = old('form_context') === 'quick_destination';
        $autoOpenQuick = $quickValidation || request('quick') === '1';
        $quickSelectedParent = old('parent_id', request('quick_parent'));
    @endphp

    <div
        data-destination-manager
        data-default-view="{{ $defaultView }}"
        data-auto-open-quick="{{ $autoOpenQuick ? '1' : '0' }}"
        data-validation-reopen="{{ $quickValidation ? '1' : '0' }}"
    >
        <x-admin.index-card
            title="Quản trị điểm đến"
            description="Quản lý cấu trúc Châu lục → Quốc gia → Khu vực → Điểm đến; thêm nhanh ngay tại đúng node cha."
            icon="bi-geo-alt"
            :create-url="route('admin.destinations.create')"
            create-label="Thêm đầy đủ"
            resource="destination"
            :order-start="$destinations->firstItem() ?? 1"
        >
            <x-slot:filters>
                <form
                    action="{{ route('admin.destinations.index') }}"
                    method="GET"
                    class="row g-3 align-items-end"
                    data-destination-list-filter
                >
                    <div class="col-xl-3 col-lg-4">
                        <label class="form-label" for="destination-search">Từ khóa</label>
                        <input
                            id="destination-search"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Tên hoặc slug điểm đến"
                        >
                    </div>

                    <div class="col-xl-3 col-lg-4">
                        <label class="form-label" for="destination-parent">Điểm đến cha</label>
                        <select id="destination-parent" name="parent_id" class="form-select">
                            <option value="">Tất cả điểm đến cha</option>
                            @foreach($parentOptions as $option)
                                <option
                                    value="{{ $option['id'] }}"
                                    @selected((string) request('parent_id') === (string) $option['id'])
                                >
                                    {{ $option['path'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-4">
                        <label class="form-label" for="destination-type">Loại</label>
                        <select id="destination-type" name="type" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-4">
                        <label class="form-label" for="destination-market">Thị trường</label>
                        <select id="destination-market" name="market" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($markets as $value => $label)
                                <option value="{{ $value }}" @selected(request('market') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-lg-4">
                        <label class="form-label" for="destination-status">Trạng thái</label>
                        <select id="destination-status" name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.destinations.index') }}" class="btn btn-default">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Xóa lọc
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-funnel me-1"></i>
                            Lọc danh sách
                        </button>
                    </div>
                </form>
            </x-slot:filters>

            <x-slot:actions>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Chế độ xem điểm đến">
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-destination-view="tree"
                        >
                            <i class="bi bi-diagram-3 me-1"></i>
                            Cây
                        </button>
                        <button
                            type="button"
                            class="btn btn-default"
                            data-destination-view="list"
                        >
                            <i class="bi bi-list-ul me-1"></i>
                            Danh sách
                        </button>
                    </div>

                    <button
                        type="button"
                        class="btn btn-success btn-sm"
                        data-quick-destination
                        data-parent-id=""
                        data-parent-name=""
                    >
                        <i class="bi bi-lightning-charge me-1"></i>
                        Thêm nhanh
                    </button>
                </div>
            </x-slot:actions>

            <div data-destination-tree-panel>
                <div class="destination-summary">
                    <div class="destination-summary__item">
                        <span class="destination-summary__value">{{ $destinationStats['total'] }}</span>
                        <span class="destination-summary__label">Điểm đến</span>
                    </div>
                    <div class="destination-summary__item">
                        <span class="destination-summary__value">{{ $destinationStats['countries'] }}</span>
                        <span class="destination-summary__label">Quốc gia</span>
                    </div>
                    <div class="destination-summary__item">
                        <span class="destination-summary__value">{{ $destinationStats['places'] }}</span>
                        <span class="destination-summary__label">Khu vực / địa điểm</span>
                    </div>
                    <div class="destination-summary__item">
                        <span class="destination-summary__value">{{ $destinationStats['missing_images'] }}</span>
                        <span class="destination-summary__label">Chưa có ảnh</span>
                    </div>
                </div>

                <div class="destination-tree-toolbar">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="search"
                            class="form-control"
                            placeholder="Tìm Trung Quốc, Nhật Bản, Thượng Hải..."
                            data-tree-search
                        >
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-default btn-sm" data-tree-expand-all>
                            <i class="bi bi-arrows-expand me-1"></i>
                            Mở tất cả
                        </button>
                        <button type="button" class="btn btn-default btn-sm" data-tree-collapse-all>
                            <i class="bi bi-arrows-collapse me-1"></i>
                            Thu gọn
                        </button>
                    </div>
                </div>

                <div class="admin-tree" data-admin-tree>
                    @forelse($destinationTree as $row)
                        @php
                            /** @var \App\Models\Destination $destination */
                            $destination = $row['destination'];
                            $hasChildren = (int) $destination->children_count > 0;
                            $canHaveChildren = in_array($destination->type, ['continent', 'country', 'region'], true);
                        @endphp

                        <x-admin.tree-row
                            :id="$destination->id"
                            :parent-id="$row['parent_id']"
                            :depth="$row['depth']"
                            :has-children="$hasChildren"
                            :expanded="$row['depth'] === 0"
                            :search="$row['path'].' '.$destination->slug"
                        >
                            <div class="admin-destination-tree__main">
                                <x-admin.thumbnail
                                    :src="$destination->cover_url"
                                    :alt="$destination->name"
                                    :href="route('admin.destinations.edit', $destination)"
                                />

                                <div class="min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <a
                                            href="{{ route('admin.destinations.edit', $destination) }}"
                                            class="fw-semibold text-decoration-none text-truncate"
                                        >
                                            {{ $destination->name }}
                                        </a>

                                        @if($destination->is_system)
                                            <span class="badge text-bg-light border">Hệ thống</span>
                                        @endif
                                    </div>

                                    <div class="small text-body-secondary text-truncate">
                                        {{ $destination->slug }}
                                    </div>
                                </div>
                            </div>

                            <div class="admin-destination-tree__meta">
                                <span class="badge text-bg-light border">
                                    {{ $types[$destination->type] ?? $destination->type }}
                                </span>
                                <span class="text-body-secondary small">
                                    {{ $destination->children_count }} điểm con
                                </span>
                                <span class="text-body-secondary small">
                                    {{ $destination->tours_count }} tour
                                </span>
                            </div>

                            <div class="admin-destination-tree__status">
                                <x-toggle
                                    model="destination"
                                    :id="$destination->id"
                                    field="is_active"
                                    :checked="$destination->is_active"
                                    :disabled="$destination->is_system"
                                />
                            </div>

                            <div class="admin-destination-tree__actions">
                                @if($canHaveChildren)
                                    <button
                                        type="button"
                                        class="btn btn-default btn-sm"
                                        data-quick-destination
                                        data-parent-id="{{ $destination->id }}"
                                        data-parent-name="{{ $destination->name }}"
                                        title="Thêm điểm đến con"
                                        data-bs-toggle="tooltip"
                                    >
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                @endif

                                <a
                                    href="{{ route('admin.destinations.edit', $destination) }}"
                                    class="btn btn-default btn-sm"
                                    title="{{ $destination->is_system ? 'Chỉnh sửa nội dung' : 'Chỉnh sửa' }}"
                                    data-bs-toggle="tooltip"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </x-admin.tree-row>
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            Chưa có điểm đến.
                        </div>
                    @endforelse
                </div>
            </div>

            <div data-destination-list-panel hidden>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th data-select-column class="text-center" style="width:48px">
                                    <input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả">
                                </th>
                                <th style="width:76px">Ảnh</th>
                                <th>Tên điểm đến</th>
                                <th>Điểm đến cha</th>
                                <th>Phân loại</th>
                                <th class="text-center">Tour</th>
                                <th class="text-center">Nổi bật</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($destinations as $destination)
                                <tr data-record-id="{{ $destination->id }}">
                                    <td data-select-column class="text-center">
                                        @if(!$destination->is_system)
                                            <input
                                                form="admin-bulk-destination-form"
                                                type="checkbox"
                                                name="ids[]"
                                                value="{{ $destination->id }}"
                                                class="form-check-input"
                                                data-check-item
                                                aria-label="Chọn {{ $destination->name }}"
                                            >
                                        @endif
                                    </td>

                                    <td>
                                        <x-admin.thumbnail
                                            :src="$destination->cover_url"
                                            :alt="$destination->name"
                                            :href="route('admin.destinations.edit', $destination)"
                                            :size="56"
                                        />
                                    </td>

                                    <td>
                                        @if($destination->is_system)
                                            <span class="fw-semibold">{{ $destination->name }}</span>
                                            <span class="badge text-bg-light border ms-1">Hệ thống</span>
                                        @else
                                            <a
                                                href="{{ route('admin.destinations.edit', $destination) }}"
                                                class="fw-semibold text-decoration-none"
                                            >
                                                {{ $destination->name }}
                                            </a>
                                        @endif

                                        <small class="d-block text-body-secondary">
                                            {{ $destination->slug }} · {{ $destination->children_count }} node con
                                        </small>
                                    </td>

                                    <td>{{ $destination->parent?->name ?? '—' }}</td>

                                    <td>
                                        <span class="badge text-bg-light border">
                                            {{ $types[$destination->type] ?? $destination->type }}
                                        </span>
                                        <small class="d-block text-body-secondary">
                                            {{ $markets[$destination->market] ?? $destination->market }}
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill text-bg-primary">
                                            {{ $destination->tours_count }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <x-toggle
                                            model="destination"
                                            :id="$destination->id"
                                            field="is_featured"
                                            :checked="$destination->is_featured"
                                            :disabled="$destination->is_system"
                                        />
                                    </td>

                                    <td class="text-center">
                                        <x-toggle
                                            model="destination"
                                            :id="$destination->id"
                                            field="is_active"
                                            :checked="$destination->is_active"
                                            :disabled="$destination->is_system"
                                        />
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a
                                                href="{{ route('admin.destinations.edit', $destination) }}"
                                                class="btn btn-default"
                                                title="{{ $destination->is_system ? 'Chỉnh sửa nội dung' : 'Chỉnh sửa' }}"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            @if(!$destination->is_system)
                                                <button
                                                    type="submit"
                                                    form="delete-destination-{{ $destination->id }}"
                                                    class="btn btn-default text-danger"
                                                    title="Xóa"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        Chưa có điểm đến phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach($destinations->where('is_system', false) as $destination)
                    <form
                        id="delete-destination-{{ $destination->id }}"
                        action="{{ route('admin.destinations.destroy', $destination) }}"
                        method="POST"
                        class="d-none"
                        data-admin-delete-form
                        data-delete-title="Xóa điểm đến này?"
                        data-delete-warning="Điểm đến đã xóa không thể khôi phục."
                    >
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach

                @if($destinations->hasPages())
                    <div class="destination-list-pagination">
                        {{ $destinations->links() }}
                    </div>
                @endif
            </div>
        </x-admin.index-card>

        <div class="modal fade" id="quickDestinationModal" tabindex="-1" aria-labelledby="quickDestinationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form
                        action="{{ route('admin.destinations.store') }}"
                        method="POST"
                        data-quick-destination-form
                    >
                        @csrf

                        <input type="hidden" name="form_context" value="quick_destination">
                        <input type="hidden" name="is_active" value="1">
                        <input type="hidden" name="sort_order" value="0">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="quickDestinationModalLabel">
                                    Thêm nhanh điểm đến
                                </h5>
                                <div class="small text-body-secondary mt-1" data-quick-parent-label>
                                    Chỉ cần tên, vị trí trong cây và ảnh đại diện.
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Đóng"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <x-input
                                        name="name"
                                        id="quick-destination-name"
                                        label="Tên điểm đến"
                                        placeholder="Ví dụ: Nhật Bản, Bắc Kinh..."
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <x-select
                                        name="parent_id"
                                        id="quick-destination-parent"
                                        label="Thuộc điểm đến"
                                        :options="collect($quickParentOptions)->mapWithKeys(fn (array $option): array => [$option['id'] => $option['path']])->all()"
                                        :selected="$quickSelectedParent"
                                        placeholder="— Điểm đến gốc —"
                                    />
                                </div>

                                <div class="col-12">
                                    <x-image-upload
                                        name="cover_image"
                                        id="quick-destination-cover"
                                        label="Ảnh đại diện"
                                    />
                                </div>

                                <div class="col-12">
                                    <div class="accordion" id="quickDestinationAdvancedAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button
                                                    class="accordion-button collapsed"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#quickDestinationAdvanced"
                                                    aria-expanded="false"
                                                    aria-controls="quickDestinationAdvanced"
                                                >
                                                    <i class="bi bi-sliders me-2"></i>
                                                    Cấu hình nâng cao
                                                </button>
                                            </h2>

                                            <div
                                                id="quickDestinationAdvanced"
                                                class="accordion-collapse collapse"
                                                data-bs-parent="#quickDestinationAdvancedAccordion"
                                            >
                                                <div class="accordion-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <x-select
                                                                name="type"
                                                                id="quick-destination-type"
                                                                label="Loại điểm đến"
                                                                :options="$types"
                                                                :selected="old('type', 'city')"
                                                                required
                                                            />
                                                        </div>

                                                        <div class="col-md-6">
                                                            <x-select
                                                                name="market"
                                                                id="quick-destination-market"
                                                                label="Thị trường"
                                                                :options="$markets"
                                                                :selected="old('market', 'international')"
                                                                required
                                                            />
                                                        </div>
                                                    </div>

                                                    <div class="form-text">
                                                        Hệ thống tự suy ra loại và thị trường theo node cha. Chỉ đổi khi cần tạo khu vực đặc biệt.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                                Hủy
                            </button>

                            <button
                                type="submit"
                                name="submit_action"
                                value="quick_create"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-plus-lg me-1"></i>
                                Thêm
                            </button>

                            <button
                                type="submit"
                                name="submit_action"
                                value="quick_create_another"
                                class="btn btn-outline-primary"
                            >
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Thêm & tiếp tục
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script type="application/json" data-destination-parent-meta>
            @json($quickParentMeta)
        </script>
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/admin-destinations.js') }}?v={{ filemtime(public_path('js/admin-destinations.js')) }}"></script>
@endpush
