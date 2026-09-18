@props([
    'model',
    'id',
    'field' => 'is_active',
    'checked' => false,
    'label' => null,
    'disabled' => false,
])

@php
    $toggleId = 'toggle_'.strtolower($model).'_'.$field.'_'.$id.'_'.Str::random(4);

    $canUpdate = ! $disabled
        && \App\Support\AdminIndexRegistry::can($model, 'update');
@endphp

@if($canUpdate)
    <div class="form-check form-switch d-inline-block">
        <input
            class="form-check-input toggle-field-switch cursor-pointer"
            type="checkbox"
            role="switch"
            id="{{ $toggleId }}"
            data-model="{{ $model }}"
            data-id="{{ $id }}"
            data-field="{{ $field }}"
            data-toggle-url="{{ route('admin.common.toggle') }}"
            aria-label="{{ $label ?: 'Thay đổi '.str_replace('_', ' ', $field) }}"
            @checked($checked)
            {{ $attributes }}
            style="width: 2.2em; height: 1.1em;"
        >

        @if($label)
            <label
                class="form-check-label ms-1 cursor-pointer fw-semibold"
                for="{{ $toggleId }}"
            >
                {{ $label }}
            </label>
        @endif
    </div>
@else
    <span class="badge {{ $checked ? 'text-bg-success' : 'text-bg-secondary' }}">
        {{ $checked ? 'Đang bật' : 'Đang tắt' }}
    </span>
@endif
