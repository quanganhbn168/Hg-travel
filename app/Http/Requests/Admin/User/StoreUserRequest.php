<?php
namespace App\Http\Requests\Admin\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreUserRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users')],'phone'=>['nullable','string','max:30'],'password'=>['required','string','min:8','confirmed'],'role'=>['required','exists:roles,name'],'is_active'=>['nullable','boolean']];} }
