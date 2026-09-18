@props([
    'name' => 'per_page',
    'label' => 'Số dòng',
    'selected' => null,
    'options' => null,
    'id' => null,
])

@php
    $options ??= \App\Support\AdminIndexRegistry::perPageOptions();
    $selected ??= request($name, $options[0] ?? 20);
    $fieldId = $id ?: 'admin-per-page-'.$name;
@endphp

<div>
    <label class="form-label" for="{{ $fieldId }}">{{ $label }}</label>
    <select id="{{ $fieldId }}" name="{{ $name }}" class="form-select">
        @foreach($options as $size)
            <option value="{{ $size }}" @selected((int) $selected === (int) $size)>
                {{ $size }}
            </option>
        @endforeach
    </select>
</div>
