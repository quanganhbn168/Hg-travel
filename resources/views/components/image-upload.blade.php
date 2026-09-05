<div class="mb-3 hg-image-upload" id="{{ $uploadId }}" data-max-files="{{ $maxFiles }}" data-max-size="{{ $maxSize }}" data-accepted-files="{{ $acceptedFiles }}" data-items="{{ json_encode($items) }}">
    @if($label)<label class="form-label fw-semibold">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>@endif
    <div class="d-flex flex-wrap gap-2 mb-2" data-media-previews></div>
    <div class="d-flex flex-wrap gap-2 mb-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-media-picker>Chọn từ thư viện</button>
        <button type="button" class="btn btn-outline-danger btn-sm" data-media-clear>Gỡ ảnh</button>
    </div>
    <div class="dropzone border rounded p-3 text-center" data-media-dropzone><div class="dz-message mb-0">{{ $placeholder }}<div class="small text-muted mt-1">{{ $acceptedFiles }} · tối đa {{ $maxSize }} MB/file @if($maxFiles > 1) · {{ $maxFiles }} ảnh/lần @endif</div></div></div>
    <div class="small mt-2" data-media-status role="status" aria-live="polite"></div>
    <input type="hidden" name="{{ $name }}" value="{{ $inputValue }}" data-media-value>
    <input type="hidden" name="{{ $name }}_remove" value="{{ $removed ? '1' : '0' }}" data-media-remove>
    @error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
