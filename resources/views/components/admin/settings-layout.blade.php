@props([
    'title',
    'description' => 'Cấu hình nội dung và cách website vận hành.',
])

<div class="row g-3 admin-settings-layout">
    <div class="col-12 col-lg-3 col-xl-3">
        <x-admin.settings-nav />
    </div>

    <section class="col-12 col-lg-9 col-xl-9 admin-settings-content">
        <div class="card card-primary card-outline mb-0">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-gear-wide-connected me-2"></i>{{ $title }}</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small border-bottom pb-3 mb-3">{{ $description }}</p>
                {{ $slot }}
            </div>
            @isset($actions)
                <div class="card-footer d-flex justify-content-end gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </section>
</div>
