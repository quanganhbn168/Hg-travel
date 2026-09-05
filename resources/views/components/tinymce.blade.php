<div class="mb-3">
    @if($label)
        <label for="{{ $inputId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    @if($translatable)
        @if($showTabs)
            <ul class="nav nav-tabs mb-2" id="{{ $inputId }}_tabs" role="tablist">
                @foreach($langs as $index => $lang)
                    @php
                        $langCode = $lang->code;
                        $tabId = $inputId . '_' . $langCode;
                        $hasError = $errors->has($name . '.' . $langCode);
                    @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }} py-1 px-3" 
                            id="{{ $tabId }}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#{{ $tabId }}" 
                            type="button" 
                            role="tab" 
                            aria-controls="{{ $tabId }}" 
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $lang->name }} 
                        @if($hasError)<span class="badge bg-danger rounded-circle ms-1" style="font-size: 0.6rem; padding: 0.2em 0.4em;">!</span>@endif
                    </button>
                </li>
            @endforeach
        </ul>
        @endif
        <div class="tab-content" id="{{ $inputId }}_tabContent">
            @foreach($langs as $index => $lang)
                @php
                    $langCode = $lang->code;
                    $tabId = $inputId . '_' . $langCode;
                    $hasError = $errors->has($name . '.' . $langCode);
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tabId }}" role="tabpanel" aria-labelledby="{{ $tabId }}-tab">
                    <textarea 
                        name="{{ $name }}[{{ $langCode }}]" 
                        id="{{ $tabId }}_field"
                        rows="{{ $rows }}"
                        class="form-control tinymce-editor {{ $hasError ? 'is-invalid' : '' }}" 
                        {{ $attributes }}
                    >{{ old($name . '.' . $langCode, $translations[$langCode] ?? '') }}</textarea>
                    @error($name . '.' . $langCode)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    @else
        <textarea 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            rows="{{ $rows }}"
            class="form-control tinymce-editor @error($name) is-invalid @enderror" 
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >{{ old($name, $value) }}</textarea>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    @endif
</div>

@pushOnce('js')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    tinymce.init({
        selector: '.tinymce-editor', license_key: 'gpl', height: 400,
        plugins: 'lists link image media table code wordcount advlist autolink charmap preview searchreplace visualblocks fullscreen',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table link image media code | preview removeformat fullscreen',
        menubar: true, branding: false, promotion: false, image_caption: true, image_title: true,
        content_style: 'body { font-family:Source Sans 3,Helvetica,Arial,sans-serif; font-size:15px }',
        relative_urls: false, remove_script_host: true, convert_urls: false,
        automatic_uploads: true, file_picker_types: 'image',
        file_picker_callback: callback => HgMedia.picker(item => callback(item.original_url, { alt: item.name })),
        images_upload_handler: async (blobInfo, progress) => {
            const editor = tinymce.activeEditor;
            const form = editor?.getElement().closest('form');
            const data = new FormData(); data.append('file', blobInfo.blob(), blobInfo.filename());
            HgMedia.busy(form, 1);
            try { const response = await HgMedia.request(window.hgMediaConfig.editor, { method: 'POST', body: data }); progress(100); return response.location; }
            finally { HgMedia.busy(form, -1); }
        },
        setup: editor => {
            editor.on('change input', () => editor.save());
            editor.on('init', () => editor.getElement().removeAttribute('required'));
            const submit = saveAndCreate => {
                editor.save();
                const form = editor.getElement().closest('form');
                if (!form) return;
                const buttons = Array.from(form.elements).filter(button => button.type === 'submit');
                const button = buttons.find(button => saveAndCreate ? button.value === 'save_and_create' : button.value !== 'save_and_create');
                button ? form.requestSubmit(button) : form.requestSubmit();
            };
            editor.addShortcut('meta+s', 'Save Form', () => submit(false));
            editor.addShortcut('meta+shift+s', 'Save and Create New', () => submit(true));
        }
    });
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => tinymce.triggerSave()));
});
</script>
@endPushOnce
