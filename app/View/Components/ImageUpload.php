<?php

namespace App\View\Components;

use App\Services\MediaPolicy;
use App\Services\MediaReferenceService;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class ImageUpload extends Component
{
    public string $uploadId;

    public string $inputValue;

    public bool $removed;

    public array $items;

    public string $acceptedFiles;

    public int $maxSize;

    public function __construct(
        public string $name, public ?string $label = null, public ?string $value = null,
        public ?string $existingUrl = null, public array $existingImages = [],
        public bool $required = false, public ?string $id = null,
        public string $placeholder = 'Kéo thả ảnh vào đây hoặc bấm để chọn file',
        public int $maxFiles = 1,
    ) {
        $this->uploadId = $id ?: 'media_'.Str::random(10);
        $this->removed = (bool) old($name.'_remove', false);
        $this->inputValue = (string) old($name, $value ?: $existingUrl ?: '');
        if ($this->removed) {
            $this->inputValue = '';
        }
        $references = app(MediaReferenceService::class);
        $this->items = array_map(fn ($path) => ['reference' => $path, 'url' => $references->url($path), 'name' => $label ?: 'Ảnh'], array_values(array_filter(explode('|', $this->inputValue))));
        $policy = app(MediaPolicy::class);
        $this->acceptedFiles = implode(',', array_map(fn ($extension) => '.'.$extension, $policy->extensions()));
        $this->maxSize = $policy->maxMegabytes();
    }

    public function render()
    {
        return view('components.image-upload');
    }
}
