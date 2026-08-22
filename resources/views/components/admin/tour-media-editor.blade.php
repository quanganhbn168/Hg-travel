@props(['tour'])

@php
    $images = $tour->relationLoaded('images') ? $tour->images : collect();
    $cover = $images->firstWhere('is_cover', true);
    $gallery = $images->reject(fn ($image) => $cover && $image->is($cover));
@endphp

<div class="row g-4">
    <div class="col-lg-5">
        <x-image-upload
            name="cover_image"
            label="Ảnh đại diện tour"
            :existing-url="$cover?->path"
            placeholder="Tải ảnh đại diện tour"
            :max-files="1"
        />
        <p class="small text-body-secondary mb-0">Ảnh đại diện xuất hiện đầu tiên ở thẻ tour, banner và album. Tải ảnh mới sẽ thay ảnh đại diện đang dùng.</p>
    </div>
    <div class="col-lg-7">
        <x-image-upload
            name="gallery_images"
            label="Ảnh bộ sưu tập"
            placeholder="Tải nhiều ảnh cho album tour"
            :max-files="12"
        />
        <p class="small text-body-secondary">Tối đa 12 ảnh mỗi lần. Có thể chọn ảnh đã có bên dưới làm ảnh đại diện.</p>

        @if ($gallery->isNotEmpty())
            <div class="row g-3 mt-1">
                @foreach ($gallery as $image)
                    <div class="col-6 col-md-4">
                        <label class="border rounded p-2 d-block h-100 bg-body-tertiary">
                            <img src="{{ \Illuminate\Support\Str::startsWith($image->path, ['http://', 'https://', '/']) ? $image->path : asset($image->path) }}" alt="{{ $image->alt_text ?: $tour->name }}" class="img-fluid rounded mb-2 w-100" style="aspect-ratio: 4 / 3; object-fit: cover;">
                            <span class="d-flex flex-column gap-1 small">
                                <span><input class="form-check-input me-1" type="radio" name="cover_image_id" value="{{ $image->id }}" @checked((string) old('cover_image_id') === (string) $image->id)> Dùng làm ảnh đại diện</span>
                                <span><input class="form-check-input me-1" type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}" @checked(in_array($image->id, old('remove_image_ids', [])))> Gỡ khỏi tour</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
