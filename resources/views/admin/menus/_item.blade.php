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
    <div class="menu-builder__drag">
        <button type="button" class="btn btn-tool menu-builder__handle" data-menu-handle aria-label="Kéo để sắp xếp" title="Kéo để sắp xếp">
            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
        </button>
    </div>

    <div class="menu-builder__body">
        <div class="menu-builder__topline">
            <div class="menu-builder__title-field">
                <label class="visually-hidden" for="menu-item-title-{{ $itemKey ?: 'new' }}">Nhãn hiển thị</label>
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
            <span class="badge text-bg-light text-truncate" data-menu-item-type>{{ $item['link_type_label'] ?? 'Liên kết' }}</span>
            <div class="card-tools ms-auto d-flex align-items-center gap-1">
                <span class="badge {{ ($item['is_active'] ?? true) ? 'text-bg-success' : 'text-bg-secondary' }}" data-menu-active-label>{{ ($item['is_active'] ?? true) ? 'Đang bật' : 'Đang tắt' }}</span>
                <button type="button" class="btn btn-tool text-danger" data-menu-remove aria-label="Xóa mục menu" title="Xóa mục menu">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="menu-builder__fields">
            <div class="menu-builder__field menu-builder__parent-field">
                <label class="form-label small mb-1" for="menu-item-parent-{{ $itemKey ?: 'new' }}">Mục cha</label>
                <select id="menu-item-parent-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="parent_key">
                    <option value="" @selected(blank($item['parent_key'] ?? null))>— Mục gốc —</option>
                    @foreach ($parentOptions as $parentOption)
                        @php($parentKey = $parentOption['key'] ?? (($parentOption['id'] ?? null) ? 'item-'.$parentOption['id'] : ''))
                        @continue($parentKey === $itemKey)
                        <option value="{{ $parentKey }}" @selected(($item['parent_key'] ?? '') === $parentKey)>{{ $parentOption['title'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="menu-builder__field menu-builder__target-field">
                <label class="form-label small mb-1" for="menu-item-target-{{ $itemKey ?: 'new' }}">Mở liên kết</label>
                <select id="menu-item-target-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="target">
                    <option value="_self" @selected(($item['target'] ?? '_self') === '_self')>Cùng tab</option>
                    <option value="_blank" @selected(($item['target'] ?? '_self') === '_blank')>Tab mới</option>
                </select>
            </div>

            <div class="menu-builder__field menu-builder__link-field">
                <span class="form-label small mb-1 d-block">Liên kết</span>
                <div class="menu-builder__link-summary text-truncate" title="{{ $item['link_summary'] ?? 'Chưa có liên kết' }}">
                    <i class="bi bi-link-45deg me-1" aria-hidden="true"></i><code data-menu-link>{{ $item['link_summary'] ?? 'Chưa có liên kết' }}</code>
                </div>
            </div>

            <div class="menu-builder__field menu-builder__active-field">
                <label class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" data-menu-field="is_active" @checked($item['is_active'] ?? true)>
                    <span class="form-check-label small">Hiển thị</span>
                </label>
            </div>
        </div>

        <div class="menu-builder__custom-url mt-2" data-menu-custom-url-wrap @unless ($isCustom) hidden @endunless>
            <label class="form-label small mb-1" for="menu-item-url-{{ $itemKey ?: 'new' }}">URL custom</label>
            <input id="menu-item-url-{{ $itemKey ?: 'new' }}" type="text" class="form-control form-control-sm" data-menu-field="url" value="{{ $item['url'] ?? '' }}" placeholder="https://... hoặc /duong-dan">
        </div>
    </div>

    <input type="hidden" data-menu-field="id" value="{{ $itemId ?? '' }}">
    <input type="hidden" data-menu-field="route_name" value="{{ $item['route_name'] ?? '' }}">
    <input type="hidden" data-menu-field="linked_source_id" value="{{ $item['linked_source_id'] ?? '' }}">
    <input type="hidden" data-menu-field="linked_source_type" value="{{ $itemType }}">
</li>
