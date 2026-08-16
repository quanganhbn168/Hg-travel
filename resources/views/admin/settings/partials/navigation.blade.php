<div class="btn-group flex-wrap mb-4" role="group" aria-label="Nhóm cài đặt">
    <a class="btn {{ request()->routeIs('admin.settings.website*') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.settings.website') }}">Website</a>
    <a class="btn {{ request()->routeIs('admin.settings.business*') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.settings.business') }}">Doanh nghiệp</a>
    <a class="btn {{ request()->routeIs('admin.settings.media*') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.settings.media') }}">Media</a>
    <a class="btn {{ request()->routeIs('admin.settings.seo*') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.settings.seo') }}">SEO</a>
    <a class="btn {{ request()->routeIs('admin.settings.contact*') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.settings.contact') }}">Liên lạc</a>
</div>
