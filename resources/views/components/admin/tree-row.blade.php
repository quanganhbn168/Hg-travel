@props([
    'id',
    'parentId' => null,
    'depth' => 0,
    'hasChildren' => false,
    'expanded' => false,
    'search' => '',
])

<div
    {{ $attributes->class(['admin-tree-row']) }}
    data-tree-node
    data-tree-id="{{ $id }}"
    data-tree-parent="{{ $parentId }}"
    data-tree-depth="{{ (int) $depth }}"
    data-tree-search="{{ $search }}"
    data-tree-has-children="{{ $hasChildren ? '1' : '0' }}"
    style="--tree-depth: {{ (int) $depth }}"
>
    <div class="admin-tree-row__branch">
        @if($hasChildren)
            <button
                type="button"
                class="btn btn-sm btn-link admin-tree-toggle"
                data-tree-toggle
                aria-expanded="{{ $expanded ? 'true' : 'false' }}"
                title="Mở/đóng điểm đến con"
            >
                <i class="bi {{ $expanded ? 'bi-chevron-down' : 'bi-chevron-right' }}" aria-hidden="true"></i>
            </button>
        @else
            <span class="admin-tree-spacer" aria-hidden="true"></span>
        @endif
    </div>

    {{ $slot }}
</div>
