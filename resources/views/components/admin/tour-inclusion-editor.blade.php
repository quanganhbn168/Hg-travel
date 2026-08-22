@props(['inclusions' => null])

@php
    $items = collect(old('inclusions', $inclusions instanceof \Illuminate\Support\Collection ? $inclusions->map(fn ($inclusion): array => [
        'type' => $inclusion->type,
        'content' => $inclusion->content,
    ])->all() : (array) $inclusions));
@endphp

<div data-tour-inclusion-editor>
    <div class="d-grid gap-2" data-tour-inclusion-list>
        @foreach ($items as $index => $item)
            @php($item = (array) $item)
            <div class="row g-2 align-items-end" data-tour-inclusion-row>
                <div class="col-md-3"><x-select name="inclusions[{{ $index }}][type]" label="Loại" :options="['included' => 'Bao gồm', 'excluded' => 'Không bao gồm']" :selected="$item['type'] ?? 'included'" :tom-select="false" /></div>
                <div class="col-md-8"><x-input name="inclusions[{{ $index }}][content]" label="Nội dung" :value="$item['content'] ?? ''" required /></div>
                <div class="col-md-1 mb-3"><button class="btn btn-outline-danger w-100" type="button" data-tour-inclusion-remove aria-label="Xóa mục"><i class="bi bi-trash"></i></button></div>
            </div>
        @endforeach
    </div>
    <template data-tour-inclusion-template>
        <div class="row g-2 align-items-end" data-tour-inclusion-row>
            <div class="col-md-3"><label class="form-label">Loại</label><select class="form-select" name="inclusions[__INDEX__][type]"><option value="included">Bao gồm</option><option value="excluded">Không bao gồm</option></select></div>
            <div class="col-md-8"><label class="form-label">Nội dung <span class="text-danger">*</span></label><input class="form-control" name="inclusions[__INDEX__][content]" required></div>
            <div class="col-md-1 mb-3"><button class="btn btn-outline-danger w-100" type="button" data-tour-inclusion-remove aria-label="Xóa mục"><i class="bi bi-trash"></i></button></div>
        </div>
    </template>
    <button class="btn btn-outline-primary mt-3" type="button" data-tour-inclusion-add><i class="bi bi-plus-circle me-1"></i>Thêm dịch vụ</button>
</div>

@pushOnce('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tour-inclusion-editor]').forEach(function (root) {
            const list = root.querySelector('[data-tour-inclusion-list]');
            const template = root.querySelector('[data-tour-inclusion-template]');
            const addButton = root.querySelector('[data-tour-inclusion-add]');
            let nextIndex = list.querySelectorAll('[data-tour-inclusion-row]').length;

            root.addEventListener('click', function (event) {
                const remove = event.target.closest('[data-tour-inclusion-remove]');
                if (remove) remove.closest('[data-tour-inclusion-row]')?.remove();
            });

            addButton.addEventListener('click', function () {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                list.appendChild(wrapper.firstElementChild);
            });
        });
    });
</script>
@endpushOnce
