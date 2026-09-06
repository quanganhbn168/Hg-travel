<details class="tour-editor-card" data-repeater-row @if ($open) open @endif>
    <summary class="tour-editor-summary">
        <button type="button" class="tour-editor-handle" data-reorder-handle aria-label="Kéo đổi thứ tự ngày; dùng phím lên xuống để di chuyển" title="Kéo để sắp xếp, hoặc dùng phím ↑ ↓"><i class="bi bi-grip-vertical" aria-hidden="true"></i></button>
        <span class="tour-editor-number" data-day-label>Ngày {{ $item['day_number'] ?? '' }}</span>
        <span class="tour-editor-row-title" data-row-title>{{ $item['title'] ?? 'Ngày mới' }}</span>
        <i class="bi bi-chevron-down tour-editor-chevron" aria-hidden="true"></i>
    </summary>
    <div class="tour-editor-fields">
        <input type="hidden" name="itineraries[{{ $index }}][day_number]" value="{{ $item['day_number'] ?? '' }}" data-day-number>
        <x-input name="itineraries[{{ $index }}][title]" label="Hành trình / tiêu đề ngày" :value="$item['title'] ?? ''" placeholder="Ví dụ: Frankfurt – Cologne" required data-title-input />
        <x-tinymce name="itineraries[{{ $index }}][description]" label="Hoạt động trong ngày" :value="\App\Support\TourContent::html($item['description'] ?? '')" rows="8" :id="'tour_itinerary_'.$index.'_description'" />
        <div class="row g-3">
            <div class="col-md-5"><x-textarea name="itineraries[{{ $index }}][meals]" label="Bữa ăn" :value="$item['meals'] ?? ''" rows="3" placeholder="Ví dụ: Sáng, trưa, tối" /></div>
            <div class="col-md-7"><x-textarea name="itineraries[{{ $index }}][accommodation]" label="Nghỉ đêm & khách sạn" :value="$item['accommodation'] ?? ''" rows="3" placeholder="Ví dụ: Khách sạn 4 sao tại Cologne" /></div>
        </div>
        <div class="tour-editor-row-footer"><small class="text-body-secondary">Bữa ăn và lưu trú hiển thị riêng ở cuối ngày. Có thể xuống dòng để tách ý.</small><button type="button" class="btn btn-sm btn-outline-danger" data-repeater-remove><i class="bi bi-trash me-1"></i>Gỡ ngày</button></div>
    </div>
</details>
