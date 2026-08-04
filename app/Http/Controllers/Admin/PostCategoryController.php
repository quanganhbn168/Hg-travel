<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPostCategoryRequest;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use App\Services\PostCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class PostCategoryController extends Controller { public function __construct(private readonly PostCategoryService $postCategoryService){} public function index(IndexPostCategoryRequest $request):View{return view('admin.post_categories.index',['categories'=>$this->postCategoryService->paginate($request->validated())]);} public function create():View{return view('admin.post_categories.create',$this->postCategoryService->formContext());} public function store(StorePostCategoryRequest $request):RedirectResponse{$category=$this->postCategoryService->create($request->validated());return redirect()->route('admin.post-categories.edit',$category)->with('success','Đã tạo danh mục bài viết.');} public function edit(PostCategory $postCategory):View{return view('admin.post_categories.edit',$this->postCategoryService->formContext($postCategory));} public function update(UpdatePostCategoryRequest $request,PostCategory $postCategory):RedirectResponse{$this->postCategoryService->update($postCategory,$request->validated());return back()->with('success','Đã cập nhật danh mục.');} public function destroy(PostCategory $postCategory):RedirectResponse{$this->postCategoryService->delete($postCategory);return back()->with('success','Đã đưa danh mục vào thùng rác.');} }
