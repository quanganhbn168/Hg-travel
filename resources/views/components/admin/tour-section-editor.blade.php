@props(['sections' => null])

@php
    $items = collect(old('sections', $sections instanceof \Illuminate\Support\Collection ? $sections->map(fn ($section): array => [
        'id' => $section->id,
        'type' => $section->type,
        'title' => $section->title,
        'content' => $section->content,
    ])->all() : (array) $sections));
@endphp

<div data-tour-section-editor>
    <div class="d-grid gap-3" data-tour-section-list>
        @foreach ($items as $index => $item)
            @php($item = (array) $item)
            <div class="border rounded-3 p-3 bg-body-tertiary" data-tour-section-row>
                <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <strong class="text-primary">Phần nội dung {{ $loop->iteration }}</strong>
                    <label class="form-check mb-0 text-danger"><input class="form-check-input" type="checkbox" name="sections[{{ $index }}][remove]" value="1" @checked($item['remove'] ?? false)> Xóa phần này</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><x-input name="sections[{{ $index }}][type]" label="Nhóm nội dung" :value="$item['type'] ?? 'other'" placeholder="Ví dụ: pricing, policy, notes" /></div>
                    <div class="col-md-8"><x-input name="sections[{{ $index }}][title]" label="Tiêu đề hiển thị" :value="$item['title'] ?? ''" placeholder="Ví dụ: Điều kiện đăng ký" /></div>
                    <div class="col-12"><x-tinymce name="sections[{{ $index }}][content]" label="Nội dung" :value="$item['content'] ?? ''" rows="7" :id="'tour_section_'.$index.'_content'" /></div>
                </div>
            </div>
        @endforeach
    </div>
    <template data-tour-section-template>
        <div class="border rounded-3 p-3 bg-body-tertiary" data-tour-section-row>
            <input type="hidden" name="sections[__INDEX__][id]" value="">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><strong class="text-primary">Phần nội dung mới</strong><button type="button" class="btn btn-sm btn-outline-danger" data-tour-section-remove><i class="bi bi-trash me-1"></i>Xóa</button></div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nhóm nội dung</label><input class="form-control" name="sections[__INDEX__][type]" value="other" placeholder="Ví dụ: pricing, policy, notes"></div>
                <div class="col-md-8"><label class="form-label">Tiêu đề hiển thị</label><input class="form-control" name="sections[__INDEX__][title]" placeholder="Ví dụ: Điều kiện đăng ký"></div>
                <div class="col-12"><label class="form-label">Nội dung</label><textarea class="form-control tinymce-editor" name="sections[__INDEX__][content]" id="tour_section___INDEX___content" rows="7"></textarea></div>
            </div>
        </div>
    </template>
    <button class="btn btn-outline-primary mt-3" type="button" data-tour-section-add><i class="bi bi-plus-circle me-1"></i>Thêm phần nội dung</button>
</div>

@pushOnce('css')
<style>
    [data-tour-section-editor] .tox-tinymce { min-height: 240px; }
</style>
@endpushOnce

@pushOnce('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tour-section-editor]').forEach(function (root) {
            const list = root.querySelector('[data-tour-section-list]');
            const template = root.querySelector('[data-tour-section-template]');
            const addButton = root.querySelector('[data-tour-section-add]');
            let nextIndex = list.querySelectorAll('[data-tour-section-row]').length;

            root.addEventListener('click', function (event) {
                const remove = event.target.closest('[data-tour-section-remove]');
                if (!remove) return;
                const row = remove.closest('[data-tour-section-row]');
                const textarea = row?.querySelector('.tinymce-editor');
                if (textarea && window.tinymce) window.tinymce.get(textarea.id)?.remove();
                row?.remove();
            });

            addButton.addEventListener('click', function () {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                const row = wrapper.firstElementChild;
                list.appendChild(row);
                window.initHgTinyMceEditors?.(row);
            });
        });
    });
</script>
@endpushOnce
