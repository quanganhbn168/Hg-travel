<?php

namespace App\Http\Requests\Admin;

use App\Services\DestinationTreeService;
use Illuminate\Foundation\Http\FormRequest;

class IndexDestinationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', 'in:active,inactive'], 'type' => ['nullable', 'in:'.implode(',', array_keys(DestinationTreeService::TYPES))], 'market' => ['nullable', 'in:'.implode(',', array_keys(DestinationTreeService::MARKETS))], 'per_page' => ['nullable', 'integer', 'in:10,15,25,50']]; }
}
