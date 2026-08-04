@extends('layouts.admin')

@section('title', 'Chỉnh sửa tour')
@section('page-title', 'Chỉnh sửa tour du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tour du lịch</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.tours.update', $tour) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8"><x-card type="primary" title="Thông tin tour" :collapsible="true"><div class="row"><div class="col-md-4"><x-input name="code" label="Mã tour" :value="$tour->code" required /></div><div class="col-md-8"><x-input name="name" label="Tên tour" :value="$tour->name" required /></div></div><x-input name="slug" label="Slug" :value="$tour->slug" required /><x-textarea name="summary" label="Mô tả ngắn" :value="$tour->summary" rows="3" /><x-textarea name="description" label="Mô tả chi tiết" :value="$tour->description" rows="9" /></x-card></div>
            <div class="col-xl-4"><x-card type="info" title="Phân loại và trạng thái" :collapsible="true" class="mb-3"><x-select name="tour_category_id" label="Danh mục tour" :options="$categories->pluck('name', 'id')->all()" :selected="$tour->tour_category_id" placeholder="Chọn danh mục" /><x-select name="destination_id" label="Điểm đến" :options="$destinations->pluck('name', 'id')->all()" :selected="$tour->destination_id" placeholder="Chọn điểm đến" /><x-select name="status" label="Trạng thái" :options="['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ']" :selected="$tour->status" /><div class="border-top pt-3"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $tour->is_active))><span class="form-check-label fw-semibold">Đang hoạt động</span></label></div><div><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="booking_open" value="1" @checked(old('booking_open', $tour->booking_open))><span class="form-check-label fw-semibold">Đang nhận booking</span></label></div></x-card><x-card type="secondary" title="Giá và thời lượng" :collapsible="true" class="mb-3"><div class="row"><div class="col-6"><x-input name="duration_days" type="number" label="Số ngày" :value="$tour->duration_days" required /></div><div class="col-6"><x-input name="duration_nights" type="number" label="Số đêm" :value="$tour->duration_nights" /></div></div><x-input name="starting_price" type="number" label="Giá từ" :value="$tour->starting_price" required /><div class="row"><div class="col-6"><x-input name="currency" label="Tiền tệ" :value="$tour->currency" /></div><div class="col-6"><x-input name="max_guests" type="number" label="Số khách tối đa" :value="$tour->max_guests" /></div></div></x-card></div>
            <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.tours.index') }}" class="btn btn-default">Hủy bỏ</a><button class="btn btn-primary">Lưu thay đổi</button></div></div></div>
        </div>
    </form>
@endsection
