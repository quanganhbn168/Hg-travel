@props(['resource'])

@if(\App\Support\AdminIndexRegistry::availableBulkActionsFor($resource) !== [])
    <th data-select-column class="text-center" style="width:48px">
        <input
            type="checkbox"
            class="form-check-input"
            data-check-all
            aria-label="Chọn tất cả"
        >
    </th>
@endif
