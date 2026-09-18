@props([
    'resetUrl',
    'submitLabel' => 'Lọc',
])

<div {{ $attributes->class(['d-flex', 'justify-content-end', 'gap-2']) }}>
    <a href="{{ $resetUrl }}" class="btn btn-default">
        <i class="bi bi-arrow-counterclockwise me-1"></i>
        Xóa lọc
    </a>
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-funnel me-1"></i>
        {{ $submitLabel }}
    </button>
</div>
