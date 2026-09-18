@extends('layouts.admin')

@section('title', 'Ưu đãi')
@section('page-title', 'Ưu đãi tour')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Ưu đãi</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách ưu đãi"
    description="Ưu đãi đang hiệu lực sẽ tự xuất hiện tại trang chủ và tour liên quan."
    :create-url="route('admin.promotions.create')"
    create-label="Thêm ưu đãi"
    resource="promotion"
>
    <x-slot:filters>
        <form action="{{ route('admin.promotions.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="promotion-search">Từ khóa</label>
                <input id="promotion-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên ưu đãi">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="promotion-status">Trạng thái</label>
                <select id="promotion-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.promotions.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="promotion" />
                    <th>Tên</th>
                    <th>Giảm</th>
                    <th>Thời gian</th>
                    <th class="text-center">Tour</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promotion)
                    <tr data-record-id="{{ $promotion->id }}">
                        <x-admin.select-item resource="promotion" :id="$promotion->id" :label="$promotion->name" />

                        <td class="fw-semibold">{{ $promotion->name }}</td>

                        <td>
                            {{ $promotion->discount_type === 'percentage'
                                ? rtrim(rtrim(number_format((float) $promotion->discount_value, 2, '.', ''), '0'), '.').'%' 
                                : number_format((float) $promotion->discount_value, 0, ',', '.').' ₫' }}
                        </td>

                        <td>
                            {{ $promotion->starts_at?->format('d/m/Y') ?? '—' }}
                            —
                            {{ $promotion->ends_at?->format('d/m/Y') ?? '—' }}
                        </td>

                        <td class="text-center">{{ $promotion->tours_count }}</td>

                        <td class="text-center">
                            <x-toggle model="promotion" :id="$promotion->id" field="is_active" :checked="$promotion->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="promotion"
                                :edit-url="route('admin.promotions.edit', $promotion)"
                                :delete-url="route('admin.promotions.destroy', $promotion)"
                                delete-title="Xóa ưu đãi này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có ưu đãi." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($promotions->hasPages())
            {{ $promotions->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
