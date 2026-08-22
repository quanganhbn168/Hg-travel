@props(['itineraries' => null])

@php
    $items = collect(old('itineraries', $itineraries instanceof \Illuminate\Support\Collection ? $itineraries->map(fn ($item): array => [
        'day_number' => $item->day_number,
        'title' => $item->title,
        'description' => $item->description,
        'meals' => $item->meals,
        'accommodation' => $item->accommodation,
    ])->all() : (array) $itineraries));
    if ($items->isEmpty()) {
        $items = collect([['day_number' => 1, 'title' => '', 'description' => '', 'meals' => '', 'accommodation' => '']]);
    }
@endphp

<div data-itinerary-editor>
    <div class="d-grid gap-3" data-itinerary-list>
        @foreach($items as $index => $item)
            @php($item = is_array($item) ? $item : (array) $item)
            <div class="border rounded-3 p-3 bg-body-tertiary" data-itinerary-row>
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <strong class="text-primary" data-itinerary-day-label>Ngày {{ $item['day_number'] ?? $loop->iteration }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-itinerary-remove><i class="bi bi-trash me-1"></i>Xóa ngày này</button>
                </div>
                <div class="row g-3">
                    <input type="hidden" name="itineraries[{{ $index }}][day_number]" value="{{ $item['day_number'] ?? $loop->iteration }}" data-itinerary-day>
                    <div class="col-md-7"><x-input name="itineraries[{{ $index }}][title]" label="Tiêu đề ngày" :value="$item['title'] ?? ''" required /></div>
                    <div class="col-md-5"><x-input name="itineraries[{{ $index }}][meals]" label="Bữa ăn" :value="$item['meals'] ?? ''" placeholder="Ví dụ: Sáng · Trưa · Tối" /><x-input name="itineraries[{{ $index }}][accommodation]" label="Lưu trú" :value="$item['accommodation'] ?? ''" placeholder="Ví dụ: Khách sạn 4 sao" /></div>
                    <div class="col-12"><x-tinymce name="itineraries[{{ $index }}][description]" label="Nội dung trong ngày" :value="$item['description'] ?? ''" rows="7" :id="'tour_itinerary_'.$index.'_description'" /></div>
                </div>
            </div>
        @endforeach
    </div>
    <template data-itinerary-template>
        <div class="border rounded-3 p-3 bg-body-tertiary" data-itinerary-row>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <strong class="text-primary" data-itinerary-day-label>Ngày __DAY__</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" data-itinerary-remove><i class="bi bi-trash me-1"></i>Xóa ngày này</button>
            </div>
            <div class="row g-3">
                <input type="hidden" name="itineraries[__INDEX__][day_number]" value="__DAY__" data-itinerary-day>
                <div class="col-md-7"><label class="form-label">Tiêu đề ngày <span class="text-danger">*</span></label><input class="form-control" name="itineraries[__INDEX__][title]" required></div>
                <div class="col-md-5"><label class="form-label">Bữa ăn</label><input class="form-control mb-3" name="itineraries[__INDEX__][meals]" placeholder="Ví dụ: Sáng · Trưa · Tối"><label class="form-label">Lưu trú</label><input class="form-control" name="itineraries[__INDEX__][accommodation]" placeholder="Ví dụ: Khách sạn 4 sao"></div>
                <div class="col-12"><label class="form-label">Nội dung trong ngày</label><textarea class="form-control tinymce-editor" name="itineraries[__INDEX__][description]" id="tour_itinerary___INDEX___description" rows="7"></textarea></div>
            </div>
        </div>
    </template>
    <button type="button" class="btn btn-outline-primary mt-3" data-itinerary-add><i class="bi bi-plus-circle me-1"></i>Thêm ngày</button>
</div>

@pushOnce('css')
<style>
    [data-itinerary-editor] .tox-tinymce { min-height: 240px; }
</style>
@endpushOnce

@pushOnce('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-itinerary-editor]').forEach(function (editorRoot) {
            const list = editorRoot.querySelector('[data-itinerary-list]');
            const template = editorRoot.querySelector('[data-itinerary-template]');
            const addButton = editorRoot.querySelector('[data-itinerary-add]');
            const durationInput = editorRoot.closest('form')?.querySelector('[name="duration_days"]');
            let nextIndex = list.querySelectorAll('[data-itinerary-row]').length;

            const syncDays = function () {
                list.querySelectorAll('[data-itinerary-row]').forEach(function (row, index) {
                    row.querySelector('[data-itinerary-day]').value = index + 1;
                    row.querySelector('[data-itinerary-day-label]').textContent = 'Ngày ' + (index + 1);
                });
            };

            editorRoot.addEventListener('click', function (event) {
                const removeButton = event.target.closest('[data-itinerary-remove]');
                if (!removeButton) return;
                const row = removeButton.closest('[data-itinerary-row]');
                const textarea = row.querySelector('.tinymce-editor');
                if (textarea && window.tinymce) window.tinymce.get(textarea.id)?.remove();
                row.remove();
                if (!list.querySelector('[data-itinerary-row]')) addButton.click();
                syncDays();
            });

            addButton.addEventListener('click', function () {
                const day = list.querySelectorAll('[data-itinerary-row]').length + 1;
                const index = nextIndex++;
                const fragment = template.content.cloneNode(true);
                const wrapper = document.createElement('div');
                wrapper.appendChild(fragment);
                wrapper.innerHTML = wrapper.innerHTML.replaceAll('__INDEX__', String(index)).replaceAll('__DAY__', String(day));
                const row = wrapper.firstElementChild;
                list.appendChild(row);
                window.initHgTinyMceEditors?.(row);
            });

            const ensureDurationDays = function () {
                const durationDays = Math.max(1, Number.parseInt(durationInput?.value || '1', 10) || 1);

                while (list.querySelectorAll('[data-itinerary-row]').length < durationDays) {
                    addButton.click();
                }
            };

            durationInput?.addEventListener('input', ensureDurationDays);
            durationInput?.addEventListener('change', ensureDurationDays);
            syncDays();
        });
    });
</script>
@endpushOnce
