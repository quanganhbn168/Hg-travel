@props(['itineraries' => null])
@php
    $items = collect(old('itineraries', $itineraries instanceof \Illuminate\Support\Collection ? $itineraries->map(fn ($item): array => $item->only(['day_number', 'title', 'description', 'meals', 'accommodation']))->all() : (array) $itineraries));
@endphp
<div data-tour-repeater="itineraries" data-itinerary-editor>
    <div class="tour-editor-toolbar">
        <span class="text-body-secondary small">Kéo tay nắm để đổi thứ tự ngày. Bấm tiêu đề để mở và chỉnh sửa.</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-expand-rows>Mở tất cả</button>
    </div>
    <div class="d-grid gap-3" data-repeater-list>
        @foreach ($items as $index => $item)
            @include('admin.tours.partials.itinerary-row', ['index' => $index, 'item' => (array) $item, 'open' => $loop->first])
        @endforeach
    </div>
    <p class="tour-editor-empty" data-repeater-empty @if ($items->isNotEmpty()) hidden @endif>Chưa có lịch trình. Thêm ngày đầu tiên để bắt đầu nhập chương trình.</p>
    <template data-repeater-template>@include('admin.tours.partials.itinerary-row', ['index' => '__INDEX__', 'item' => [], 'open' => true])</template>
    <button type="button" class="btn btn-outline-primary mt-3" data-repeater-add><i class="bi bi-plus-circle me-1"></i>Thêm ngày</button>
    <div class="tour-editor-undo mt-3" data-repeater-undo hidden><span>Đã gỡ ngày khỏi bản nhập.</span> <button type="button" class="btn btn-sm btn-link" data-undo-remove>Hoàn tác</button></div>
</div>
