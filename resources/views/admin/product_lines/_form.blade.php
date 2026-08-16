<div class="row g-3">
    <div class="col-xl-8">
        <x-card type="primary" title="Nội dung landing page" :collapsible="true">
            <div class="row"><div class="col-md-8"><x-input name="name" label="Tên giải pháp" :value="$productLine->name" required /></div><div class="col-md-4"><x-input name="icon" label="Bootstrap icon" :value="$productLine->icon" placeholder="bi-buildings" /></div></div>
            <x-input name="slug" label="Slug" :value="$productLine->slug" required />
            <x-input name="kicker" label="Kicker / nhóm nhỏ" :value="$productLine->kicker" placeholder="MICE · Hội nghị · Sự kiện" />
            <x-textarea name="summary" label="Mô tả ngắn" :value="$productLine->summary" rows="3" required />
            <x-textarea name="description" label="Nội dung giới thiệu landing page" :value="$productLine->description" rows="7" required />
            <x-textarea name="benefits" label="Điểm nổi bật" :value="implode(PHP_EOL, $productLine->benefits ?: [])" rows="5" placeholder="Mỗi ý một dòng" />
        </x-card>
        <x-card type="secondary" title="SEO" :collapsible="true">
            <x-input name="seo_title" label="SEO title" :value="$productLine->seo_title" />
            <x-textarea name="seo_description" label="SEO description" :value="$productLine->seo_description" rows="3" />
        </x-card>
    </div>
    <div class="col-xl-4">
        <x-card type="info" title="Hiển thị" :collapsible="true" class="mb-3">
            <x-image-upload name="hero_image" label="Ảnh hero" :value="$productLine->hero_image" />
            <x-image-upload name="cover_image" label="Ảnh cover" :value="$productLine->cover_image" />
            <x-input name="sort_order" type="number" label="Thứ tự" :value="$productLine->sort_order ?: 0" />
            <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $productLine->exists ? $productLine->is_active : true))><span class="form-check-label fw-semibold">Kích hoạt</span></label>
            <label class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="is_home" value="1" @checked(old('is_home', $productLine->is_home))><span class="form-check-label fw-semibold">Hiển thị ở trang chủ</span></label>
        </x-card>
        <x-card type="warning" title="Hành trình liên quan" :collapsible="true" class="mb-3">
            @php($selectedTours = old('tours', $productLine->exists ? $productLine->tours->pluck('id')->all() : []))
            <label class="form-label" for="product-line-tours">Chọn nhiều tour</label><select class="form-select" id="product-line-tours" name="tours[]" multiple size="9">@foreach($tours as $tour)<option value="{{ $tour->id }}" @selected(in_array($tour->id, $selectedTours))>{{ $tour->code }} · {{ $tour->name }}</option>@endforeach</select><small class="form-text text-muted">Giữ Ctrl/Cmd để chọn nhiều tour.</small>
        </x-card>
        <x-card type="success" title="Dịch vụ đồng hành" :collapsible="true">
            @php($selectedServices = old('services', $productLine->exists ? $productLine->services->pluck('id')->all() : []))
            <label class="form-label" for="product-line-services">Chọn nhiều dịch vụ</label><select class="form-select" id="product-line-services" name="services[]" multiple size="9">@foreach($services as $service)<option value="{{ $service->id }}" @selected(in_array($service->id, $selectedServices))>{{ $service->category?->name }} · {{ $service->name }}</option>@endforeach</select><small class="form-text text-muted">Các hạng mục có thể kết hợp trong giải pháp.</small>
        </x-card>
    </div>
    <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.product-lines.index') }}" class="btn btn-default">Hủy bỏ</a><button class="btn btn-primary">{{ $submitLabel }}</button></div></div></div>
</div>
