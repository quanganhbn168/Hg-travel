@php
    $schedules = $tour->relationLoaded('schedules') ? $tour->schedules : collect();
    $itineraries = $tour->relationLoaded('itineraries') ? $tour->itineraries : collect();
    $sections = $tour->relationLoaded('sections') ? $tour->sections : collect();
    $inclusions = $tour->relationLoaded('inclusions') ? $tour->inclusions : collect();
    $errorKeys = collect($errors->keys());
    $hasTabError = static function (array $prefixes) use ($errorKeys): bool {
        return $errorKeys->contains(static fn (string $key): bool => collect($prefixes)->contains(static fn (string $prefix): bool => $key === $prefix || str_starts_with($key, $prefix . '.')));
    };
    $activeTab = match (true) {
        $hasTabError(['schedules']) => 'schedules',
        $hasTabError(['description', 'itineraries', 'sections', 'inclusions']) => 'content',
        $hasTabError(['banner_image', 'cover_image', 'gallery_images', 'cover_image_id', 'remove_image_ids']) => 'media',
        $hasTabError(['seo_title', 'seo_description', 'published_at', 'sort_order']) => 'seo',
        default => 'general',
    };
@endphp

<form id="admin-save-form" action="{{ $isEditing ? route('admin.tours.update', $tour) : route('admin.tours.store') }}" method="POST">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="card card-primary card-outline mb-0">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="bi bi-map me-2"></i>{{ $isEditing ? 'Thiết lập tour' : 'Tạo tour mới' }}</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-4" id="tour-form-tabs" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link @if ($activeTab === 'general') active @endif" id="tour-general-tab" data-bs-toggle="tab" data-bs-target="#tour-general" type="button" role="tab" aria-controls="tour-general" aria-selected="{{ $activeTab === 'general' ? 'true' : 'false' }}"><i class="bi bi-info-circle me-1"></i>Thông tin tour</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link @if ($activeTab === 'schedules') active @endif" id="tour-schedules-tab" data-bs-toggle="tab" data-bs-target="#tour-schedules" type="button" role="tab" aria-controls="tour-schedules" aria-selected="{{ $activeTab === 'schedules' ? 'true' : 'false' }}"><i class="bi bi-calendar3 me-1"></i>Lịch khởi hành <span class="badge text-bg-secondary ms-1">{{ $schedules->count() }}</span></button></li>
                <li class="nav-item" role="presentation"><button class="nav-link @if ($activeTab === 'content') active @endif" id="tour-content-tab" data-bs-toggle="tab" data-bs-target="#tour-content" type="button" role="tab" aria-controls="tour-content" aria-selected="{{ $activeTab === 'content' ? 'true' : 'false' }}"><i class="bi bi-journal-text me-1"></i>Chương trình tour</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link @if ($activeTab === 'media') active @endif" id="tour-media-tab" data-bs-toggle="tab" data-bs-target="#tour-media" type="button" role="tab" aria-controls="tour-media" aria-selected="{{ $activeTab === 'media' ? 'true' : 'false' }}"><i class="bi bi-images me-1"></i>Hình ảnh</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link @if ($activeTab === 'seo') active @endif" id="tour-seo-tab" data-bs-toggle="tab" data-bs-target="#tour-seo" type="button" role="tab" aria-controls="tour-seo" aria-selected="{{ $activeTab === 'seo' ? 'true' : 'false' }}"><i class="bi bi-search me-1"></i>SEO & xuất bản</button></li>
            </ul>

            <div class="tab-content">
                <section class="tab-pane fade @if ($activeTab === 'general') show active @endif" id="tour-general" role="tabpanel" aria-labelledby="tour-general-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-xl-8">
                            <h4 class="h6 text-primary mb-3">Thông tin nhận diện</h4>
                            <div class="row">
                                <div class="col-md-4"><x-input name="code" label="Mã tour" :value="$tour->code" required /></div>
                                <div class="col-md-8"><x-input name="name" label="Tên tour" :value="$tour->name" required /></div>
                            </div>
                            <x-input name="slug" label="Slug" :value="$tour->slug" required />
                            <x-textarea name="summary" label="Mô tả ngắn" :value="$tour->summary" rows="4" />
                        </div>
                        <div class="col-xl-4">
                            <div class="border rounded-3 p-3 bg-body-tertiary h-100">
                                <h4 class="h6 text-primary mb-3">Phân loại và trạng thái</h4>
                                <x-select name="tour_category_ids" label="Loại hình tour" :options="$categories->pluck('name', 'id')->all()" :selected="old('tour_category_ids', $tour->categories->pluck('id')->all())" multiple placeholder="Chọn một hoặc nhiều loại hình" />
                                <x-select name="destination_ids" label="Điểm đến" :options="collect($destinationOptions)->mapWithKeys(fn (array $option): array => [$option['id'] => $option['label']])->all()" :selected="old('destination_ids', $tour->destinations->pluck('id')->all())" multiple placeholder="Chọn một hoặc nhiều điểm đến" />
                                <x-select name="status" label="Trạng thái" :options="['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ']" :selected="old('status', $tour->status ?? 'draft')" />
                                <div class="border-top pt-3">
                                    <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $tour->is_active ?? true))><span class="form-check-label fw-semibold">Đang hoạt động</span></label>
                                    <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="booking_open" value="1" @checked(old('booking_open', $tour->booking_open ?? true))><span class="form-check-label fw-semibold">Đang nhận booking</span></label>
                                    <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $tour->is_featured ?? false))><span class="form-check-label fw-semibold">Tour nổi bật</span></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top mt-4 pt-4">
                        <h4 class="h6 text-primary mb-3">Giá cơ bản và thời lượng</h4>
                        <div class="row">
                            <div class="col-sm-6 col-lg-3"><x-input name="duration_days" type="number" min="1" step="1" label="Số ngày" :value="$tour->duration_days ?? 1" required /></div>
                            <div class="col-sm-6 col-lg-3"><x-input name="duration_nights" type="number" min="0" step="1" label="Số đêm" :value="$tour->duration_nights ?? 0" /></div>
                            <div class="col-sm-6 col-lg-3"><x-money-input name="starting_price" label="Giá từ" :value="$tour->starting_price ?? 0" :currency="$tour->currency ?? 'VND'" :min="0" required /></div>
                            <div class="col-sm-6 col-lg-3"><x-input name="max_guests" type="number" min="0" step="1" label="Số khách tối đa" :value="$tour->max_guests" /></div>
                            <div class="col-sm-6"><x-input name="currency" label="Tiền tệ" :value="$tour->currency ?? 'VND'" /></div>
                            <div class="col-sm-6"><x-input name="transport" label="Phương tiện chung" :value="$tour->transport" placeholder="Ví dụ: Máy bay · Ô tô du lịch" /></div>
                        </div>
                        <p class="form-text mb-0">Giá và số khách từng đợt có thể thiết lập riêng ở tab <strong>Lịch khởi hành</strong>.</p>
                    </div>
                </section>

                <section class="tab-pane fade @if ($activeTab === 'schedules') show active @endif" id="tour-schedules" role="tabpanel" aria-labelledby="tour-schedules-tab" tabindex="0">
                    <h4 class="h6 text-primary mb-2">Thiết lập ngày khởi hành, giá và số chỗ</h4>
                    <p class="text-body-secondary small">Mỗi đợt có ngày đi/về, giá niêm yết, giá giảm và số khách tối đa riêng. Số chỗ đã giữ tự cập nhật từ booking; lịch đã có booking chỉ được đóng hoặc hủy, không xóa.</p>
                    <x-admin.tour-schedule-editor :schedules="$schedules" />
                </section>

                <section class="tab-pane fade @if ($activeTab === 'content') show active @endif" id="tour-content" role="tabpanel" aria-labelledby="tour-content-tab" tabindex="0">
                    <div class="mb-4">
                        <h4 class="h6 text-primary mb-3">Mô tả chi tiết</h4>
                        <x-tinymce name="description" label="Nội dung giới thiệu tour" :value="$tour->description" rows="10" />
                    </div>
                    <div class="border-top pt-4 mb-4">
                        <h4 class="h6 text-primary mb-2">Lịch trình theo ngày</h4>
                        <div class="alert alert-info small py-2 mb-3"><strong>Cách nhập dễ đọc:</strong> tạo từng ngày riêng; trong mỗi ngày tách ý thành đoạn ngắn hoặc danh sách gạch đầu dòng bằng thanh công cụ. Không dồn toàn bộ lịch trình thành một đoạn dài. Có thể thêm, xóa hoặc sắp xếp lại từng ngày trước khi lưu.</div>
                        <x-admin.tour-itinerary-editor :itineraries="$itineraries" />
                    </div>
                    <div class="border-top pt-4 mb-4">
                        <h4 class="h6 text-primary mb-2">Nội dung bổ sung</h4>
                        <p class="text-body-secondary small">Dùng cho điều kiện, chính sách, lưu ý, bảng giá hoặc các phần thông tin riêng của tour.</p>
                        <x-admin.tour-section-editor :sections="$sections" />
                    </div>
                    <div class="border-top pt-4">
                        <h4 class="h6 text-primary mb-3">Dịch vụ bao gồm / không bao gồm</h4>
                        <x-admin.tour-inclusion-editor :inclusions="$inclusions" />
                    </div>
                </section>

                <section class="tab-pane fade @if ($activeTab === 'media') show active @endif" id="tour-media" role="tabpanel" aria-labelledby="tour-media-tab" tabindex="0">
                    <h4 class="h6 text-primary mb-2">Ảnh đại diện và bộ sưu tập</h4>
                    <p class="text-body-secondary small mb-4">Ảnh đại diện dùng ở thẻ tour và trang chi tiết; bộ sưu tập hiển thị trong album tour.</p>
                    <x-admin.tour-media-editor :tour="$tour" />
                </section>

                <section class="tab-pane fade @if ($activeTab === 'seo') show active @endif" id="tour-seo" role="tabpanel" aria-labelledby="tour-seo-tab" tabindex="0">
                    <div class="row">
                        <div class="col-xl-8">
                            <h4 class="h6 text-primary mb-3">Tối ưu tìm kiếm</h4>
                            <x-input name="seo_title" label="SEO title" :value="$tour->seo_title" />
                            <x-textarea name="seo_description" label="SEO description" :value="$tour->seo_description" rows="4" />
                        </div>
                        <div class="col-xl-4">
                            <div class="border rounded-3 p-3 bg-body-tertiary h-100">
                                <h4 class="h6 text-primary mb-3">Xuất bản và sắp xếp</h4>
                                <x-input name="published_at" type="datetime-local" label="Ngày xuất bản" :value="$tour->published_at?->format('Y-m-d\\TH:i')" />
                                <x-input name="sort_order" type="number" min="0" step="1" label="Thứ tự hiển thị" :value="$tour->sort_order ?? 0" />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ route('admin.tours.index') }}" class="btn btn-default">Hủy bỏ</a>
            @if (! $isEditing)
                <button type="submit" name="submit_action" value="save_and_create" class="btn btn-outline-primary">Lưu & tạo mới</button>
            @endif
            <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Lưu thay đổi' : 'Lưu tour' }}</button>
        </div>
    </div>
</form>
