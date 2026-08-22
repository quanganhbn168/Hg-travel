<?php
namespace App\Http\Requests\Admin\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateUserRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{$user=$this->route('user');return ['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users')->ignore($user)],'phone'=>['nullable','string','max:30'],'password'=>['nullable','string','min:8','confirmed'],'role'=>['required',Rule::exists('roles','name')->where(fn ($query) => $query->where('guard_name','web'))],'is_active'=>['nullable','boolean']];} }
