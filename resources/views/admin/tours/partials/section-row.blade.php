@php
    $type = $item['type'] ?? 'other';
    $options = $sectionTypes;
    if (! array_key_exists($type, $options)) $options[$type] = 'Nhóm đã lưu: '.$type;
@endphp
<details class="tour-editor-card" data-repeater-row @if ($open) open @endif @if ($item['remove'] ?? false) hidden @endif>
    <summary class="tour-editor-summary">
        <button type="button" class="tour-editor-handle" data-reorder-handle aria-label="Kéo đổi thứ tự mục; dùng phím lên xuống để di chuyển" title="Kéo để sắp xếp, hoặc dùng phím ↑ ↓"><i class="bi bi-grip-vertical" aria-hidden="true"></i></button>
        <span class="tour-editor-row-title" data-row-title>{{ ($item['title'] ?? '') ?: ($options[$type] ?? 'Mục mới') }}</span>
        <span class="badge text-bg-light" data-row-type>{{ $options[$type] }}</span>
        <i class="bi bi-chevron-down tour-editor-chevron" aria-hidden="true"></i>
    </summary>
    <div class="tour-editor-fields">
        <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
        <input type="hidden" name="sections[{{ $index }}][remove]" value="{{ ($item['remove'] ?? false) ? 1 : 0 }}" data-remove-input>
        <div class="row g-3">
            <div class="col-md-4"><x-select name="sections[{{ $index }}][type]" label="Nhóm nội dung" :options="$options" :selected="$type" :tom-select="false" data-section-type /></div>
            <div class="col-md-8"><x-input name="sections[{{ $index }}][title]" label="Tiêu đề hiển thị" :value="$item['title'] ?? ''" placeholder="Để trống để dùng tên nhóm nội dung" data-title-input /></div>
        </div>
        <p class="form-text" data-section-placement>{{ $type === 'highlights' ? 'Hiển thị trước lịch trình.' : 'Hiển thị dạng mục thu gọn sau lịch trình.' }} Giá và ngày khởi hành được cập nhật ở tab Lịch khởi hành.</p>
        <x-tinymce name="sections[{{ $index }}][content]" label="Nội dung" :value="\App\Support\TourContent::html($item['content'] ?? '')" rows="8" :id="'tour_section_'.$index.'_content'" />
        <div class="tour-editor-row-footer"><span></span><button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove><i class="bi bi-trash me-1"></i>Gỡ mục</button></div>
    </div>
</details>
