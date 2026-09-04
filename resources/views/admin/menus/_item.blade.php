@props(['item', 'parentOptions' => []])

@php
    $itemId = $item['id'] ?? null;
    $itemKey = $item['key'] ?? ($itemId ? 'item-'.$itemId : '');
    $itemType = $item['linked_source_type'] ?? 'custom';
    $itemTitle = $item['title'] ?? 'Mục menu mới';
    $isCustom = $itemType === 'custom';
@endphp

<tr
    class="menu-builder__row"
    data-menu-node
    data-item-id="{{ $itemId ?? '' }}"
    data-menu-key="{{ $itemKey }}"
    data-parent-key="{{ $item['parent_key'] ?? '' }}"
>
    <td class="text-center menu-builder__drag-cell">
        <button type="button" class="btn btn-tool menu-builder__handle" data-menu-handle aria-label="Kéo để sắp xếp" title="Kéo để sắp xếp">
            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
        </button>
    </td>
    <td class="menu-builder__title-cell">
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
        <span class="badge text-bg-light mt-1" data-menu-item-type>{{ $item['link_type_label'] ?? 'Liên kết' }}</span>
        <input type="hidden" data-menu-field="id" value="{{ $itemId ?? '' }}">
        <input type="hidden" data-menu-field="route_name" value="{{ $item['route_name'] ?? '' }}">
        <input type="hidden" data-menu-field="linked_source_id" value="{{ $item['linked_source_id'] ?? '' }}">
        <input type="hidden" data-menu-field="linked_source_type" value="{{ $itemType }}">
    </td>
    <td class="menu-builder__parent-cell">
        <label class="visually-hidden" for="menu-item-parent-{{ $itemKey ?: 'new' }}">Mục cha</label>
        <select id="menu-item-parent-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="parent_key">
            <option value="" @selected(blank($item['parent_key'] ?? null))>— Mục gốc —</option>
            @foreach ($parentOptions as $parentOption)
                @php($parentKey = $parentOption['key'] ?? (($parentOption['id'] ?? null) ? 'item-'.$parentOption['id'] : ''))
                @continue($parentKey === $itemKey)
                <option value="{{ $parentKey }}" @selected(($item['parent_key'] ?? '') === $parentKey)>{{ $parentOption['title'] }}</option>
            @endforeach
        </select>
    </td>
    <td class="menu-builder__link-cell">
        @if ($isCustom)
            <label class="visually-hidden" for="menu-item-url-{{ $itemKey ?: 'new' }}">URL custom</label>
            <input id="menu-item-url-{{ $itemKey ?: 'new' }}" type="text" class="form-control form-control-sm" data-menu-field="url" value="{{ $item['url'] ?? '' }}" placeholder="https://... hoặc /duong-dan">
        @else
            <div class="menu-builder__link-summary text-truncate" title="{{ $item['link_summary'] ?? 'Chưa có liên kết' }}">
                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i><code data-menu-link>{{ $item['link_summary'] ?? 'Chưa có liên kết' }}</code>
            </div>
        @endif
    </td>
    <td>
        <label class="visually-hidden" for="menu-item-target-{{ $itemKey ?: 'new' }}">Mở liên kết</label>
        <select id="menu-item-target-{{ $itemKey ?: 'new' }}" class="form-select form-select-sm" data-menu-field="target">
            <option value="_self" @selected(($item['target'] ?? '_self') === '_self')>Cùng tab</option>
            <option value="_blank" @selected(($item['target'] ?? '_self') === '_blank')>Tab mới</option>
        </select>
    </td>
    <td class="text-center">
        <label class="form-check form-switch d-inline-flex align-items-center justify-content-center mb-0" title="Bật/tắt mục menu">
            <input class="form-check-input" type="checkbox" data-menu-field="is_active" @checked($item['is_active'] ?? true)>
            <span class="visually-hidden">Hiển thị</span>
        </label>
        <span class="visually-hidden" data-menu-active-label>{{ ($item['is_active'] ?? true) ? 'Đang bật' : 'Đang tắt' }}</span>
    </td>
    <td class="text-end">
        <button type="button" class="btn btn-tool text-danger" data-menu-remove aria-label="Xóa mục menu" title="Xóa mục menu">
            <i class="bi bi-trash" aria-hidden="true"></i>
        </button>
    </td>

</tr>
