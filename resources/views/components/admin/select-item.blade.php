@props([
    'resource',
    'id',
    'label' => 'bản ghi',
    'disabled' => false,
])

@if(! $disabled && \App\Support\AdminIndexRegistry::availableBulkActionsFor($resource) !== [])
    <td data-select-column class="text-center">
        <input
            form="{{ \App\Support\AdminIndexRegistry::formIdFor($resource) }}"
            type="checkbox"
            name="ids[]"
            value="{{ $id }}"
            class="form-check-input"
            data-check-item
            aria-label="Chọn {{ $label }}"
        >
    </td>
@elseif(\App\Support\AdminIndexRegistry::availableBulkActionsFor($resource) !== [])
    <td data-select-column class="text-center"></td>
@endif
