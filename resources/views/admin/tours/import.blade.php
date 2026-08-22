@extends('layouts.admin')

@section('title', 'Nhập dữ liệu tour')
@section('page-title', 'Nhập dữ liệu tour')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tour du lịch</a></li>
        <li class="breadcrumb-item active">Nhập dữ liệu</li>
    </ol>
@endsection

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Nhập tour và lịch khởi hành</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-4" id="tour-import-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="schedule-import-tab" data-bs-toggle="pill" data-bs-target="#schedule-import-pane" type="button" role="tab" aria-controls="schedule-import-pane" aria-selected="true">Lịch khởi hành Excel</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="package-import-tab" data-bs-toggle="pill" data-bs-target="#package-import-pane" type="button" role="tab" aria-controls="package-import-pane" aria-selected="false">Gói ZIP đầy đủ</button>
                </li>
            </ul>

            <div class="tab-content" id="tour-import-tab-content">
                <div class="tab-pane fade show active" id="schedule-import-pane" role="tabpanel" aria-labelledby="schedule-import-tab" tabindex="0">
                    <form action="{{ route('admin.tours.import.schedules.prepare') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <div class="alert alert-light border mb-0">
                                Dùng đúng mẫu Google Sheet anh gửi: <strong>TÊN TOUR</strong>, <strong>THỜI GIAN</strong>, <strong>PHƯƠNG TIỆN DI CHUYỂN</strong>, <strong>LỊCH KHỞI HÀNH</strong>, <strong>GIÁ TRỌN GÓI</strong>, <strong>COM</strong>, <strong>SỐ CHỖ</strong>.
                                Hệ thống tách từng ngày như “Tháng 10: 13, 28, 30”, đổi giá <code>23.990K</code> thành <code>23.990.000 ₫</code>, và để anh ghép tên tour trước khi lưu.
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label for="schedule_file" class="form-label">File Excel (.xlsx)</label>
                            <input id="schedule_file" name="schedule_file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="form-control @error('schedule_file') is-invalid @enderror">
                            @error('schedule_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Tải từ Google Sheets: Tệp → Tải xuống → Microsoft Excel (.xlsx).</div>
                        </div>
                        <div class="col-lg-6">
                            <label for="google_sheet_url" class="form-label">Hoặc link Google Sheet công khai</label>
                            <input id="google_sheet_url" name="google_sheet_url" type="url" value="{{ old('google_sheet_url') }}" class="form-control @error('google_sheet_url') is-invalid @enderror" placeholder="https://docs.google.com/spreadsheets/d/...">
                            @error('google_sheet_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Link cần được cấp quyền xem bằng liên kết; nếu không, hãy tải file Excel lên.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="import_year" class="form-label">Năm cho các dòng chỉ ghi “Tháng …”</label>
                            <input id="import_year" name="import_year" type="number" min="2020" max="2100" value="{{ old('import_year', now()->year) }}" class="form-control @error('import_year') is-invalid @enderror" required>
                            @error('import_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Kiểm tra và đối chiếu tour</button>
                            <a href="{{ route('admin.tours.index') }}" class="btn btn-default">Quay lại</a>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="package-import-pane" role="tabpanel" aria-labelledby="package-import-tab" tabindex="0">
                    <form action="{{ route('admin.tours.import.package') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                Gói ZIP HGTRIP dùng để nhập toàn bộ tour, nội dung, lịch, thư viện ảnh và bản lưu nguồn. Tour trùng <strong>mã tour</strong> sẽ được cập nhật; lịch trình, ngày khởi hành, nội dung phần và ảnh có trong gói sẽ thay thế dữ liệu tương ứng.
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <label for="package_file" class="form-label">Gói HGTRIP (.zip)</label>
                            <input id="package_file" name="package_file" type="file" accept=".zip,application/zip" class="form-control @error('package_file') is-invalid @enderror" required>
                            @error('package_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Gói cần có README.txt, manifest.json, hgtrip_import.xlsx, ảnh và dữ liệu nguồn đi kèm.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input id="confirm_package_import" name="confirm_package_import" value="1" type="checkbox" class="form-check-input @error('confirm_package_import') is-invalid @enderror" required>
                                <label for="confirm_package_import" class="form-check-label">Tôi xác nhận cập nhật dữ liệu tour theo gói ZIP này.</label>
                                @error('confirm_package_import')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-down me-1"></i>Nhập gói ZIP</button>
                            <a href="{{ route('admin.tours.index') }}" class="btn btn-default">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
