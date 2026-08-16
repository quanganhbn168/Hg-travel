<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexServiceCategoryRequest;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\ServiceCatalogAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function __construct(private readonly ServiceCatalogAdminService $catalog) {}
    public function index(IndexServiceCategoryRequest $request): View { return view('admin.service_categories.index', ['categories' => $this->catalog->categories($request->validated())]); }
    public function create(): View { return view('admin.service_categories.create', $this->catalog->categoryContext()); }
    public function store(StoreServiceCategoryRequest $request): RedirectResponse { $category = $this->catalog->createCategory($request->validated()); return to_route('admin.service-categories.edit', $category)->with('success', 'Đã tạo danh mục dịch vụ.'); }
    public function edit(ServiceCategory $serviceCategory): View { return view('admin.service_categories.edit', $this->catalog->categoryContext($serviceCategory)); }
    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse { $this->catalog->updateCategory($serviceCategory, $request->validated()); return back()->with('success', 'Đã cập nhật danh mục dịch vụ.'); }
    public function destroy(ServiceCategory $serviceCategory): RedirectResponse { $this->catalog->deleteCategory($serviceCategory); return to_route('admin.service-categories.index')->with('success', 'Đã xóa danh mục dịch vụ.'); }
}
