<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class StorePostCategoryRequest extends FormRequest { public function authorize(): bool { return true; } protected function prepareForValidation(): void { $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name')))]); } public function rules(): array { return ['parent_id' => ['nullable','integer','exists:post_categories,id'], 'name' => ['required','string','max:255'], 'slug' => ['required','string','max:255',Rule::unique('post_categories'),Rule::unique('slugs','slug')->where('locale',app()->getLocale())], 'description' => ['nullable','string'], 'seo_title' => ['nullable','string','max:255'], 'seo_description' => ['nullable','string'], 'sort_order' => ['nullable','integer','min:0'], 'is_active' => ['nullable','boolean']]; } }
