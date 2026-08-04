<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPostRequest;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class PostController extends Controller { public function __construct(private readonly PostService $postService){} public function index(IndexPostRequest $request):View{return view('admin.posts.index',['posts'=>$this->postService->paginate($request->validated())]);} public function create():View{return view('admin.posts.create',$this->postService->formContext());} public function store(StorePostRequest $request):RedirectResponse{$post=$this->postService->create($request->validated());return redirect()->route('admin.posts.edit',$post)->with('success','Đã tạo bài viết.');} public function edit(Post $post):View{return view('admin.posts.edit',$this->postService->formContext($post));} public function update(UpdatePostRequest $request,Post $post):RedirectResponse{$this->postService->update($post,$request->validated());return back()->with('success','Đã cập nhật bài viết.');} public function destroy(Post $post):RedirectResponse{$this->postService->delete($post);return back()->with('success','Đã đưa bài viết vào thùng rác.');} }
