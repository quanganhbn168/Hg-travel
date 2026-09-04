@props(['item'])

@php
    $itemId = $item['id'] ?? null;
    $itemType = $item['linked_source_type'] ?? 'custom';
    $itemTitle = $item['title'] ?? 'Mục menu mới';
@endphp

<li class="menu-builder__node" data-menu-node data-item-id="{{ $itemId ?? '' }}">
    <div class="menu-builder__item card card-outline mb-2">
        <div class="card-header py-2">
            <div class="menu-builder__identity">
                <button type="button" class="btn btn-tool menu-builder__handle" data-menu-handle aria-label="Kéo để sắp xếp">
                    <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                </button>
                <button type="button" class="btn btn-tool" data-menu-toggle aria-expanded="false" aria-label="Mở chỉnh sửa mục menu">
                    <i class="bi bi-chevron-right" data-menu-toggle-icon aria-hidden="true"></i>
                </button>
                <strong class="menu-builder__label text-truncate" data-menu-item-label>{{ $itemTitle }}</strong>
                <span class="badge text-bg-light text-truncate" data-menu-item-type>{{ $item['link_type_label'] ?? 'Liên kết' }}</span>
            </div>
            <div class="card-tools">
                <span class="badge text-bg-success" data-menu-active-label>{{ ($item['is_active'] ?? true) ? 'Đang bật' : 'Đang tắt' }}</span>
                <button type="button" class="btn btn-tool text-danger" data-menu-remove aria-label="Xóa mục menu" title="Xóa mục menu">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="card-body py-3" data-menu-editor hidden>
            <div class="row g-3">
                <div class="col-lg-7">
                    <label class="form-label" for="menu-item-title-{{ $itemId ?: 'new' }}">Nhãn hiển thị <span class="text-danger">*</span></label>
                    <input id="menu-item-title-{{ $itemId ?: 'new' }}" type="text" class="form-control" data-menu-field="title" value="{{ $itemTitle }}" maxlength="255" required>
                    <div class="form-text">Có thể rút gọn nhãn, còn liên kết nguồn vẫn được giữ.</div>
                </div>
                <div class="col-lg-5">
                    <label class="form-label" for="menu-item-target-{{ $itemId ?: 'new' }}">Cách mở liên kết</label>
                    <select id="menu-item-target-{{ $itemId ?: 'new' }}" class="form-select" data-menu-field="target">
                        <option value="_self" @selected(($item['target'] ?? '_self') === '_self')>Cùng tab</option>
                        <option value="_blank" @selected(($item['target'] ?? '_self') === '_blank')>Tab mới</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="menu-builder__link-summary">
                        <span class="text-body-secondary small">Liên kết đang dùng</span>
                        <code data-menu-link>{{ $item['link_summary'] ?? 'Chưa có liên kết' }}</code>
                    </div>
                </div>
                <div class="col-lg-8">
                    <label class="form-label" for="menu-item-url-{{ $itemId ?: 'new' }}">URL custom</label>
                    <input id="menu-item-url-{{ $itemId ?: 'new' }}" type="text" class="form-control" data-menu-field="url" value="{{ $item['url'] ?? '' }}" placeholder="https://... hoặc /duong-dan">
                    <div class="form-text">Chỉ dùng khi mục này là liên kết custom.</div>
                </div>
                <div class="col-lg-4 d-flex align-items-end">
                    <label class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" data-menu-field="is_active" @checked($item['is_active'] ?? true)>
                        <span class="form-check-label">Hiển thị mục này</span>
                    </label>
                </div>
            </div>
            <input type="hidden" data-menu-field="id" value="{{ $itemId ?? '' }}">
            <input type="hidden" data-menu-field="route_name" value="{{ $item['route_name'] ?? '' }}">
            <input type="hidden" data-menu-field="linked_source_id" value="{{ $item['linked_source_id'] ?? '' }}">
            <input type="hidden" data-menu-field="linked_source_type" value="{{ $itemType }}">
        </div>
    </div>

    <ul class="menu-builder__list list-unstyled" data-menu-list>
        @forelse($item['children'] ?? [] as $child)
            @include('admin.menus._item', ['item' => $child])
        @empty
            <li class="menu-builder__empty" data-menu-empty>Thả mục vào đây để tạo cấp con.</li>
        @endforelse
    </ul>
</li>
