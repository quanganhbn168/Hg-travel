@props(['sections' => null, 'group' => 'details'])
@php
    $items = collect(old('sections', $sections instanceof \Illuminate\Support\Collection ? $sections->map(fn ($section): array => $section->only(['id', 'type', 'title', 'content']))->all() : (array) $sections))
        ->filter(fn ($item) => (($item['type'] ?? 'other') === 'highlights') === ($group === 'highlights'));
    $sectionTypes = \App\Support\TourContent::sectionTypes();
@endphp
<div data-tour-repeater="sections" data-index-prefix="{{ $group }}" data-tour-section-editor>
    <div class="tour-editor-toolbar"><span class="text-body-secondary small">{{ $group === 'highlights' ? 'Hiển thị trước lịch trình chi tiết trên trang tour.' : 'Khách bấm mở từng mục ở cuối lịch trình. Kéo để đổi thứ tự các mục.' }}</span><button type="button" class="btn btn-sm btn-outline-secondary" data-expand-rows>Mở tất cả</button></div>
    <div class="d-grid gap-3" data-repeater-list>
        @foreach ($items as $index => $item)
            @include('admin.tours.partials.section-row', ['index' => $index, 'item' => (array) $item, 'open' => $loop->first])
        @endforeach
    </div>
    <p class="tour-editor-empty" data-repeater-empty @if ($items->isNotEmpty()) hidden @endif>Chưa có nội dung trong nhóm này.</p>
    <template data-repeater-template>@include('admin.tours.partials.section-row', ['index' => '__INDEX__', 'item' => ['type' => $group === 'highlights' ? 'highlights' : 'other'], 'open' => true])</template>
    <button type="button" class="btn btn-outline-primary mt-3" data-repeater-add><i class="bi bi-plus-circle me-1"></i>{{ $group === 'highlights' ? 'Thêm điểm nổi bật' : 'Thêm mục thông tin' }}</button>
    <div class="tour-editor-undo mt-3" data-repeater-undo hidden><span>Đã gỡ mục khỏi bản nhập.</span> <button type="button" class="btn btn-sm btn-link" data-undo-remove>Hoàn tác</button></div>
</div>
