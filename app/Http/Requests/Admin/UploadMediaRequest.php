<?php

namespace App\Http\Requests\Admin;

use App\Services\MediaPolicy;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $policy = app(MediaPolicy::class);
        $imagesOnly = ! $this->routeIs('admin.media.upload.library');

        return [
            'file' => $policy->rules($imagesOnly),
        ];
    }
}
