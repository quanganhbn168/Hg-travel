@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'placeholder' => '',
    'currency' => 'VND',
    'decimals' => 0,
    'id' => null,
    'useOld' => true,
    'min' => null,
])

@php
    $inputId = $id ?? 'money_input_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $rawValue = trim((string) ($useOld ? old($name, $value) : $value));
    $numericValue = preg_match('/^-?\d+(?:\.\d+)?$/', $rawValue) ? $rawValue : '';
    if ($numericValue !== '' && (int) $decimals === 0) {
        $numericValue = (string) (int) floor((float) $numericValue);
    }
    $displayValue = $numericValue === '' ? '' : number_format((float) $numericValue, (int) $decimals, ',', '.');
    $hasError = $errors->has($name);
@endphp

<div class="mb-3" data-money-input data-money-decimals="{{ (int) $decimals }}">
    @if($label)
        <label for="{{ $inputId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="input-group">
        <input
            type="text"
            id="{{ $inputId }}"
            class="form-control {{ $hasError ? 'is-invalid' : '' }}"
            value="{{ $displayValue }}"
            inputmode="{{ (int) $decimals === 0 ? 'numeric' : 'decimal' }}"
            autocomplete="off"
            data-money-display
            placeholder="{{ $placeholder ?: ($label ? 'Nhập ' . strtolower($label) . '...' : '') }}"
            @required($required)
            @if($min !== null) min="{{ $min }}" @endif
            @disabled($attributes->get('disabled'))
        >
        <span class="input-group-text">{{ $currency }}</span>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $numericValue }}" data-money-raw>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('js')
        <script>
            (() => {
                const normalizeMoney = (value, decimals) => {
                    let normalized = (value ?? '').toString().trim().replace(/\s/g, '').replace(/[^0-9.,-]/g, '');
                    if (!normalized || normalized === '-') return '';

                    if (decimals === 0) return normalized.replace(/[.,]/g, '');

                    const lastDot = normalized.lastIndexOf('.');
                    const lastComma = normalized.lastIndexOf(',');
                    if (lastComma > lastDot) normalized = normalized.replace(/\./g, '').replace(',', '.');
                    else if (lastDot >= 0) normalized = normalized.replace(/,/g, '');
                    else normalized = normalized.replace(',', '.');

                    const [whole, decimal = ''] = normalized.split('.');
                    return decimal ? `${whole}.${decimal.slice(0, decimals)}` : whole;
                };

                const formatMoney = (value, decimals) => {
                    if (!value) return '';
                    const numberValue = Number(value);
                    return Number.isFinite(numberValue)
                        ? new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 0, maximumFractionDigits: decimals }).format(numberValue)
                        : '';
                };

                window.initHgMoneyInputs = (scope = document) => {
                    scope.querySelectorAll('[data-money-input]:not([data-money-ready])').forEach((root) => {
                        const display = root.querySelector('[data-money-display]');
                        const raw = root.querySelector('[data-money-raw]');
                        if (!display || !raw) return;

                        const decimals = Number(root.dataset.moneyDecimals || 0);
                        const sync = (value) => {
                            const normalized = normalizeMoney(value, decimals);
                            raw.value = normalized;
                            display.value = formatMoney(normalized, decimals);
                        };

                        display.addEventListener('input', () => sync(display.value));
                        display.addEventListener('blur', () => sync(display.value));
                        root.dataset.moneyReady = '1';
                        sync(raw.value || display.value);
                    });
                };

                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => window.initHgMoneyInputs());
                else window.initHgMoneyInputs();
            })();
        </script>
    @endpush
@endonce
