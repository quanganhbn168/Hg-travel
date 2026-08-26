@foreach ($items as $item)
    @php
        $hasChildren = ! empty($item['children']);
        $isRoot = $level === 0;
        $itemClasses = $isRoot ? 'nav-item' . ($hasChildren ? ' dropdown' : '') : ($hasChildren ? 'dropdown-submenu' : '');
        $linkClasses = $isRoot ? 'nav-link' : 'dropdown-item';
    @endphp

    <li class="{{ $itemClasses }}">
        <a class="{{ $linkClasses }} {{ ($item['active'] ?? false) ? 'active' : '' }} {{ $hasChildren && $isRoot ? 'dropdown-toggle' : '' }}"
           href="{{ $item['url'] }}"
           target="{{ $item['target'] }}"
           @if ($item['target'] === '_blank') rel="noopener noreferrer" @endif
           @if ($hasChildren && $isRoot) data-bs-toggle="dropdown" aria-expanded="false" @endif>
            {{ $item['title'] }}
        </a>

        @if ($hasChildren)
            <ul class="{{ $isRoot ? 'dropdown-menu' : ($context === 'mobile' ? 'mobile-submenu' : 'dropdown-menu dropdown-menu-nested') }}">
                @include('components.frontend.menu-items', ['items' => $item['children'], 'level' => $level + 1, 'context' => $context])
            </ul>
        @endif
    </li>
@endforeach
