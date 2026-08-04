<?php
namespace App\Http\Requests\Admin\User;
use Illuminate\Foundation\Http\FormRequest;
class IndexUserRequest extends FormRequest { public function authorize():bool{return true;} public function rules():array{return ['search'=>['nullable','string','max:255'],'per_page'=>['nullable','integer','in:10,20,25,50']];} }
