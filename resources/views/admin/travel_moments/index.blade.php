@extends('layouts.admin')

@section('title', 'Khoảnh khắc')
@section('page-title', 'Khoảnh khắc')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Khoảnh khắc</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Thư viện khoảnh khắc"
    description="Quản lý ảnh cảm hứng trên trang chủ và nhóm hiển thị của GLightbox."
    :create-url="route('admin.travel-moments.create')"
    create-label="Thêm khoảnh khắc"
    resource="travel_moment"
    :order-start="$moments->firstItem() ?? 1"
>
    <x-slot:filters>
        <form action="{{ route('admin.travel-moments.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label" for="moment-search">Từ khóa</label>
                <input id="moment-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc slug">
            </div>

            <div class="col-lg-3">
                <label class="form-label" for="moment-group">Nhóm</label>
                <select id="moment-group" name="group_id" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected((string) request('group_id') === (string) $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="moment-status">Trạng thái</label>
                <select id="moment-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-1">
                <x-admin.per-page />
            </div>

            <div class="col-lg-2">
                <x-admin.filter-actions :reset-url="route('admin.travel-moments.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="travel_moment" />
                    <th style="width:90px">Ảnh</th>
                    <th>Khoảnh khắc</th>
                    <th>Nhóm GLightbox</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($moments as $moment)
                    <tr data-record-id="{{ $moment->id }}">
                        <x-admin.select-item resource="travel_moment" :id="$moment->id" :label="$moment->title" />

                        <td>
                            <x-admin.thumbnail
                                :src="$moment->image_preview_url"
                                :alt="$moment->alt_text ?: $moment->title"
                                :href="route('admin.travel-moments.edit', $moment)"
                                :size="64"
                            />
                        </td>

                        <td>
                            <a href="{{ route('admin.travel-moments.edit', $moment) }}" class="fw-semibold text-decoration-none">
                                {{ $moment->title }}
                            </a>
                            <small class="d-block text-body-secondary">{{ $moment->slug }}</small>
                        </td>

                        <td>
                            <span class="badge text-bg-light border">{{ $moment->group?->name ?: '—' }}</span>
                            <small class="d-block text-body-secondary">
                                data-gallery: moments-{{ $moment->group?->slug }}
                            </small>
                        </td>

                        <td class="text-center">
                            <x-toggle model="travel_moment" :id="$moment->id" field="is_active" :checked="$moment->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="travel_moment"
                                :edit-url="route('admin.travel-moments.edit', $moment)"
                                :delete-url="route('admin.travel-moments.destroy', $moment)"
                                delete-title="Xóa khoảnh khắc này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có khoảnh khắc." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($moments->hasPages())
            {{ $moments->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
