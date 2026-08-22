<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
class UserService { public function paginate(array $filters=[]):LengthAwarePaginator{return User::with('roles')->when(filled($filters['search']??null),fn($q)=>$q->where(fn($inner)=>$inner->where('name','like','%'.trim($filters['search']).'%')->orWhere('email','like','%'.trim($filters['search']).'%')))->latest()->paginate((int)($filters['per_page']??20))->withQueryString();} public function formContext(User $user=null):array{return ['user'=>$user?:new User(['is_active'=>true]),'roles'=>Role::query()->where('guard_name','web')->orderBy('name')->pluck('name','name')];} public function create(array $data):User{$user=User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']??null,'password'=>Hash::make($data['password']),'is_active'=>(bool)($data['is_active']??false),'email_verified_at'=>now()]);$user->syncRoles([$data['role']]);return $user;} public function update(User $user,array $data):void{$payload=['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']??null,'is_active'=>(bool)($data['is_active']??false)];if(filled($data['password']??null))$payload['password']=Hash::make($data['password']);$user->update($payload);$user->syncRoles([$data['role']]);} }
