@extends('layouts.admin')

@section('title', 'Mã giảm giá')
@section('page-title', 'Mã giảm giá')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mã giảm giá</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách coupon"
    description="Quản lý coupon theo từng tour hoặc toàn bộ hệ thống."
    :create-url="route('admin.coupons.create')"
    create-label="Thêm mã giảm giá"
    resource="coupon"
>
    <x-slot:filters>
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="coupon-search">Từ khóa</label>
                <input id="coupon-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã hoặc tên coupon">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="coupon-status">Trạng thái</label>
                <select id="coupon-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.coupons.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="coupon" />
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Giảm</th>
                    <th>Đã dùng</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr data-record-id="{{ $coupon->id }}">
                        <x-admin.select-item resource="coupon" :id="$coupon->id" :label="$coupon->code" />

                        <td><code>{{ $coupon->code }}</code></td>
                        <td class="fw-semibold">{{ $coupon->name }}</td>

                        <td>
                            {{ $coupon->discount_type === 'percentage'
                                ? rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.').'%' 
                                : number_format((float) $coupon->discount_value, 0, ',', '.').' ₫' }}
                        </td>

                        <td>
                            {{ $coupon->used_count }}
                            @if($coupon->usage_limit)
                                / {{ $coupon->usage_limit }}
                            @endif
                        </td>

                        <td class="text-center">
                            <x-toggle model="coupon" :id="$coupon->id" field="is_active" :checked="$coupon->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="coupon"
                                :edit-url="route('admin.coupons.edit', $coupon)"
                                :delete-url="route('admin.coupons.destroy', $coupon)"
                                delete-title="Xóa mã giảm giá này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có coupon." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($coupons->hasPages())
            {{ $coupons->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
