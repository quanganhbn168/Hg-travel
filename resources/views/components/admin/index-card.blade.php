@props([
    'title',
    'description' => null,
    'createUrl' => null,
    'createLabel' => 'Thêm mới',
    'resource' => null,
    'orderStart' => 1,
])

@php
    $resource = is_string($resource) && $resource !== '' ? $resource : null;

    $bulkActions = $resource
        ? \App\Support\AdminIndexRegistry::availableBulkActionsFor($resource)
        : [];

    $hasBulkActions = $resource !== null && $bulkActions !== [];

    $showReorder = $resource !== null
        && \App\Support\AdminIndexRegistry::orderColumnFor($resource) !== null
        && \App\Support\AdminIndexRegistry::can($resource, 'update');

    $reorderEnabled = $showReorder
        && ! request()->hasAny(
            \App\Support\AdminIndexRegistry::reorderFiltersFor($resource)
        );

    $bulkFormId = $resource
        ? \App\Support\AdminIndexRegistry::formIdFor($resource)
        : null;

    $canCreate = $createUrl
        && (
            $resource === null
            || \App\Support\AdminIndexRegistry::can($resource, 'create')
        );
@endphp

<div
    data-admin-index
    @if($resource) data-index-resource="{{ $resource }}" @endif
    @if($hasBulkActions) data-bulk-form-id="{{ $bulkFormId }}" @endif
    @if($showReorder)
        data-reorderable="1"
        data-reorder-enabled="{{ $reorderEnabled ? '1' : '0' }}"
        data-reorder-url="{{ route('admin.common.reorder') }}"
        data-order-start="{{ $orderStart }}"
    @endif
    {{ $attributes }}
>
    <x-admin.index-header
        :description="$description"
        :create-url="$canCreate ? $createUrl : null"
        :create-label="$createLabel"
    />

    @isset($filters)
        <x-admin.filter-panel>{{ $filters }}</x-admin.filter-panel>
    @endisset

    @if($hasBulkActions)
        <x-admin.bulk-toolbar
            :form-id="$bulkFormId"
            :resource="$resource"
            :actions="$bulkActions"
            :delete-warning="\App\Support\AdminIndexRegistry::deleteWarningFor($resource)"
        />
    @endif

    <x-admin.table-card :title="$title">
        <x-slot:tools>
            @isset($actions)
                {{ $actions }}
            @endisset

            @if($showReorder)
                <button
                    type="button"
                    class="btn btn-default btn-sm"
                    data-reorder-toggle
                    @disabled(! $reorderEnabled)
                    aria-pressed="false"
                    title="{{ $reorderEnabled ? 'Sắp xếp thứ tự hiển thị' : 'Bỏ bộ lọc để sắp xếp' }}"
                >
                    <i class="bi bi-arrow-down-up me-1"></i>
                    <span data-reorder-label>Sắp xếp</span>
                </button>
            @endif
        </x-slot:tools>

        {{ $slot }}

        @isset($footer)
            <x-slot:footer>{{ $footer }}</x-slot:footer>
        @endisset
    </x-admin.table-card>
</div>
