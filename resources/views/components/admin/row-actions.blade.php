@props([
    'resource',
    'editUrl' => null,
    'viewUrl' => null,
    'deleteUrl' => null,
    'editTitle' => 'Chỉnh sửa',
    'viewTitle' => 'Xem trang',
    'deleteTitle' => 'Xóa bản ghi này?',
    'deleteWarning' => null,
    'allowEdit' => true,
    'allowDelete' => true,
    'viewTarget' => '_blank',
])

@php
    $canUpdate = $allowEdit
        && $editUrl
        && \App\Support\AdminIndexRegistry::can($resource, 'update');

    $canDelete = $allowDelete
        && $deleteUrl
        && \App\Support\AdminIndexRegistry::can($resource, 'delete');

    $deleteWarning ??= \App\Support\AdminIndexRegistry::deleteWarningFor($resource);
@endphp

@if($viewUrl || $canUpdate || $canDelete || isset($before) || isset($after))
    <div class="btn-group btn-group-sm" role="group">
        @isset($before)
            {{ $before }}
        @endisset

        @if($viewUrl)
            <a
                href="{{ $viewUrl }}"
                class="btn btn-default"
                target="{{ $viewTarget }}"
                @if($viewTarget === '_blank') rel="noopener" @endif
                title="{{ $viewTitle }}"
                aria-label="{{ $viewTitle }}"
            >
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        @endif

        @if($canUpdate)
            <a
                href="{{ $editUrl }}"
                class="btn btn-default"
                title="{{ $editTitle }}"
                aria-label="{{ $editTitle }}"
            >
                <i class="bi bi-pencil-square"></i>
            </a>
        @endif

        @if($canDelete)
            <form
                action="{{ $deleteUrl }}"
                method="POST"
                class="d-inline"
                data-admin-delete-form
                data-delete-title="{{ $deleteTitle }}"
                data-delete-warning="{{ $deleteWarning }}"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="btn btn-default text-danger"
                    title="Xóa"
                    aria-label="Xóa"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif

        @isset($after)
            {{ $after }}
        @endisset
    </div>
@endif
