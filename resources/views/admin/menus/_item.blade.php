@props(['item', 'parentOptions' => []])

@php
    $itemId = $item['id'] ?? null;
    $itemKey = $item['key'] ?? ($itemId ? 'item-'.$itemId : '');
    $itemType = $item['linked_source_type'] ?? 'custom';
    $itemTitle = $item['title'] ?? 'Mục menu mới';
    $isCustom = $itemType === 'custom';
@endphp

<li
    class="menu-builder__row"
    data-menu-node
    data-item-id="{{ $itemId ?? '' }}"
    data-menu-key="{{ $itemKey }}"
    data-parent-key="{{ $item['parent_key'] ?? '' }}"
>
    <div class="menu-builder__bar">
        <button type="button" class="btn btn-tool menu-builder__handle" data-menu-handle aria-label="Kéo để sắp xếp" title="Kéo để sắp xếp">
            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-tool menu-builder__toggle" data-menu-toggle aria-expanded="false" aria-label="Mở cấu hình mục menu" title="Mở cấu hình mục menu">
            <i class="bi bi-chevron-right" data-menu-toggle-icon aria-hidden="true"></i>
        </button>

        <div class="menu-builder__identity">
            <strong
                class="menu-builder__label text-truncate"
                data-menu-item-label
                title="{{ $itemTitle }}"
                data-bs-toggle="tooltip"
                data-bs-title="{{ $itemTitle }}"
                data-bs-placement="top"
            >{{ $itemTitle }}</strong>
            <span class="badge text-bg-light menu-builder__type" data-menu-item-type>{{ $item['link_type_label'] ?? 'Liên kết' }}</span>
        </div>

        <div class="card-tools">
            <button type="button" class="btn btn-tool text-danger" data-menu-remove aria-label="Xóa mục menu" title="Xóa mục menu">
                <i class="bi bi-trash" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="menu-builder__editor" data-menu-editor hidden>
        <div class="row g-3">
            <div class="col-lg-5">
                <label class="form-label small fw-semibold" for="menu-item-title-{{ $itemKey ?: 'new' }}">Nhãn hiển thị</label>
                <input
                    id="menu-item-title-{{ $itemKey ?: 'new' }}"
                    type="text"
                    class="form-control form-control-sm"
                    data-menu-field="title"
                    value="{{ $itemTitle }}"
                    maxlength="255"
                    placeholder="Nhãn hiển thị"
                    required
                >
            </div>
            <div class="col-lg-3">
                <label class="form-label small fw-semibold" for="menu-item-parent-{{ $itemKey ?: 'new' }}">Mục cha</label>
                <select id="menu-item-parent-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="parent_key">
                    <option value="" @selected(blank($item['parent_key'] ?? null))>— Mục gốc —</option>
                    @foreach ($parentOptions as $parentOption)
                        @php($parentKey = $parentOption['key'] ?? (($parentOption['id'] ?? null) ? 'item-'.$parentOption['id'] : ''))
                        @continue($parentKey === $itemKey)
                        <option value="{{ $parentKey }}" @selected(($item['parent_key'] ?? '') === $parentKey)>{{ $parentOption['title'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label small fw-semibold" for="menu-item-target-{{ $itemKey ?: 'new' }}">Mở liên kết</label>
                <select id="menu-item-target-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="target">
                    <option value="_self" @selected(($item['target'] ?? '_self') === '_self')>Cùng tab</option>
                    <option value="_blank" @selected(($item['target'] ?? '_self') === '_blank')>Tab mới</option>
                </select>
            </div>
            <div class="col-lg-2 d-flex align-items-end">
                <label class="form-check form-switch mb-1" title="Bật/tắt mục menu">
                    <input class="form-check-input" type="checkbox" data-menu-field="is_active" @checked($item['is_active'] ?? true)>
                    <span class="form-check-label small">Hiển thị</span>
                </label>
            </div>

            <div class="col-12">
                <div class="small text-body-secondary mb-1">Liên kết</div>
                <div class="menu-builder__link-summary" data-menu-link-summary>
                    <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>
                    <code data-menu-link>{{ $item['link_summary'] ?? ($isCustom ? 'Chưa có liên kết' : 'Liên kết nguồn') }}</code>
                </div>
            </div>

            <div class="col-12" data-menu-custom-url-wrap @if (! $isCustom) hidden @endif>
                <label class="form-label small fw-semibold" for="menu-item-url-{{ $itemKey ?: 'new' }}">URL custom</label>
                <input
                    id="menu-item-url-{{ $itemKey ?: 'new' }}"
                    type="text"
                    class="form-control form-control-sm"
                    data-menu-field="url"
                    value="{{ $item['url'] ?? '' }}"
                    placeholder="https://... hoặc /duong-dan"
                >
            </div>
        </div>

        <input type="hidden" data-menu-field="id" value="{{ $itemId ?? '' }}">
        <input type="hidden" data-menu-field="route_name" value="{{ $item['route_name'] ?? '' }}">
        <input type="hidden" data-menu-field="linked_source_id" value="{{ $item['linked_source_id'] ?? '' }}">
        <input type="hidden" data-menu-field="linked_source_type" value="{{ $itemType }}">
    </div>
</li>
