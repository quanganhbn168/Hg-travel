<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class StorePostRequest extends FormRequest { public function authorize(): bool{return true;} protected function prepareForValidation():void{$this->merge(['slug'=>Str::slug((string)($this->input('slug')?:$this->input('name')))]);} public function rules():array{return ['post_category_id'=>['nullable','integer','exists:post_categories,id'],'name'=>['required','string','max:255'],'slug'=>['required','string','max:255',Rule::unique('posts'),Rule::unique('slugs','slug')->where('locale',app()->getLocale())],'summary'=>['nullable','string'],'content'=>['nullable','string'],'cover_image'=>['nullable','string','max:4096'],'seo_title'=>['nullable','string','max:255'],'seo_description'=>['nullable','string'],'seo_keywords'=>['nullable','string'],'is_featured'=>['nullable','boolean'],'is_active'=>['nullable','boolean'],'published_at'=>['nullable','date']];} }
