@extends('layouts.admin')

@section('title', 'Cảm nhận khách hàng')
@section('page-title', 'Cảm nhận khách hàng')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cảm nhận khách hàng</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách cảm nhận"
    description="Các cảm nhận được hiển thị tại trang chủ."
    :create-url="route('admin.testimonials.create')"
    create-label="Thêm cảm nhận"
    resource="testimonial"
    :order-start="$testimonials->firstItem() ?? 1"
>
    <x-slot:filters>
        <form action="{{ route('admin.testimonials.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="testimonial-search">Từ khóa</label>
                <input id="testimonial-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Khách hàng hoặc nội dung">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="testimonial-status">Trạng thái</label>
                <select id="testimonial-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.testimonials.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="testimonial" />
                    <th style="width:76px">Ảnh</th>
                    <th>Khách hàng</th>
                    <th>Nội dung</th>
                    <th class="text-center">Đánh giá</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($testimonials as $testimonial)
                    <tr data-record-id="{{ $testimonial->id }}">
                        <x-admin.select-item resource="testimonial" :id="$testimonial->id" :label="$testimonial->customer_name" />

                        <td>
                            <x-admin.thumbnail
                                :src="$testimonial->avatar_url"
                                :alt="$testimonial->customer_name"
                                :href="route('admin.testimonials.edit', $testimonial)"
                                :size="56"
                                icon="bi-person"
                            />
                        </td>

                        <td>
                            <strong>{{ $testimonial->customer_name }}</strong>
                            <small class="d-block text-body-secondary">
                                {{ $testimonial->customer_title ?: '—' }}
                            </small>
                        </td>

                        <td>{{ IlluminateSupportStr::limit($testimonial->content, 90) }}</td>
                        <td class="text-center">{{ $testimonial->rating }}/5</td>

                        <td class="text-center">
                            <x-toggle model="testimonial" :id="$testimonial->id" field="is_active" :checked="$testimonial->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="testimonial"
                                :edit-url="route('admin.testimonials.edit', $testimonial)"
                                :delete-url="route('admin.testimonials.destroy', $testimonial)"
                                delete-title="Xóa cảm nhận này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có cảm nhận." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($testimonials->hasPages())
            {{ $testimonials->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
