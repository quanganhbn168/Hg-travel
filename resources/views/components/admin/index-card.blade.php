@props([
    'title',
    'description' => null,
    'icon' => 'bi-list-ul',
    'createUrl' => null,
    'createLabel' => 'Thêm mới',
    'collapsible' => true,
    'maximizable' => true,
    'resource' => null,
    'bulkActions' => [],
    'bulkDeleteWarning' => null,
    'reorderable' => null,
    'reorderEnabled' => null,
    'orderStart' => 1,
])

@php
    $resource = is_string($resource) && $resource !== '' ? $resource : null;
    $registryActions = $resource ? \App\Support\AdminIndexRegistry::bulkActionsFor($resource) : [];
    $bulkActions = $registryActions !== [] ? $registryActions : $bulkActions;
    $bulkDeleteWarning = $resource
        ? \App\Support\AdminIndexRegistry::deleteWarningFor($resource)
        : ($bulkDeleteWarning ?: 'Dữ liệu đã xóa không thể khôi phục.');
    $hasBulkActions = $resource !== null && $registryActions !== [];
    $canReorderResource = $resource !== null && \App\Support\AdminIndexRegistry::orderColumnFor($resource) !== null;
    // Registry capabilities are authoritative; legacy props remain accepted for compatibility.
    $reorderConfigured = $canReorderResource;
    $reorderEnabled = ! request()->hasAny(
        $resource ? \App\Support\AdminIndexRegistry::reorderFiltersFor($resource) : []
    );
    $canReorder = $resource !== null
        && $reorderConfigured
        && $reorderEnabled
        && $canReorderResource;
    $showReorder = $resource !== null && $reorderConfigured && $canReorderResource;
    $bulkFormId = $resource ? \App\Support\AdminIndexRegistry::formIdFor($resource) : null;
@endphp

<div
    data-admin-index
    @if($resource) data-index-resource="{{ $resource }}" @endif
    @if($hasBulkActions) data-bulk-form-id="{{ $bulkFormId }}" @endif
    @if($showReorder) data-reorderable="1" data-reorder-enabled="{{ $canReorder ? '1' : '0' }}" data-reorder-url="{{ route('admin.common.reorder') }}" data-order-start="{{ $orderStart }}" @endif
    {{ $attributes }}
>
    <x-admin.index-header :description="$description" :create-url="$createUrl" :create-label="$createLabel" />

    @isset($filters)
        <x-admin.filter-panel>{{ $filters }}</x-admin.filter-panel>
    @endisset

    @if($hasBulkActions)
        <x-admin.bulk-toolbar :form-id="$bulkFormId" :resource="$resource" :actions="$bulkActions" :delete-warning="$bulkDeleteWarning" />
    @endif

    <x-admin.table-card :title="$title">
        <x-slot:tools>
            @isset($actions){{ $actions }}@endisset
            @if($showReorder)
                <button type="button" class="btn btn-default btn-sm" data-reorder-toggle @disabled(! $canReorder) aria-pressed="false">
                    <i class="bi bi-arrow-down-up me-1"></i><span data-reorder-label>Sắp xếp</span>
                </button>
            @endif
        </x-slot:tools>
        {{ $slot }}
        @isset($footer)
            <x-slot:footer>{{ $footer }}</x-slot:footer>
        @endisset
    </x-admin.table-card>
</div>
