<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Admin\User\IndexUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class UserController extends Controller { public function __construct(private readonly UserService $userService){} public function index(IndexUserRequest $request):View{return view('admin.users.index',['users'=>$this->userService->paginate($request->validated())]);} public function create():View{return view('admin.users.create',$this->userService->formContext());} public function store(StoreUserRequest $request):RedirectResponse{$user=$this->userService->create($request->validated());return redirect()->route('admin.users.edit',$user)->with('success','Đã tạo tài khoản.');} public function edit(User $user):View{return view('admin.users.edit',$this->userService->formContext($user));} public function update(UpdateUserRequest $request,User $user):RedirectResponse{$this->userService->update($user,$request->validated());return back()->with('success','Đã cập nhật tài khoản.');} }
