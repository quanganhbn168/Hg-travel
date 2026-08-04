<?php
namespace App\Http\Requests\Admin;
use Illuminate\Validation\Rule;
class UpdatePostRequest extends StorePostRequest { public function rules():array{$post=$this->route('post');$rules=parent::rules();$rules['slug']=['required','string','max:255',Rule::unique('posts','slug')->ignore($post),Rule::unique('slugs','slug')->where(fn($query)=>$query->where('locale',app()->getLocale())->where(fn($inner)=>$inner->where('sluggable_type','!=',$post->getMorphClass())->orWhere('sluggable_id','!=',$post->getKey())))];return $rules;} }
