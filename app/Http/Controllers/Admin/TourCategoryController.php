<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTourCategoryRequest;
use App\Http\Requests\Admin\StoreTourCategoryRequest;
use App\Http\Requests\Admin\UpdateTourCategoryRequest;
use App\Models\TourCategory;
use App\Services\TourCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TourCategoryController extends Controller
{
    public function __construct(private readonly TourCategoryService $tourCategoryService) {}

    public function index(IndexTourCategoryRequest $request): View
    {
        return view('admin.tour_categories.index', [
            'categories' => $this->tourCategoryService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.tour_categories.create', $this->tourCategoryService->formContext());
    }

    public function store(StoreTourCategoryRequest $request): RedirectResponse
    {
        $category = $this->tourCategoryService->create($request->validated());

        return redirect()->route('admin.tour-categories.edit', $category)
            ->with('success', 'Đã tạo danh mục tour.');
    }

    public function edit(TourCategory $tourCategory): View
    {
        return view('admin.tour_categories.edit', $this->tourCategoryService->formContext($tourCategory));
    }

    public function update(UpdateTourCategoryRequest $request, TourCategory $tourCategory): RedirectResponse
    {
        $this->tourCategoryService->update($tourCategory, $request->validated());

        return back()->with('success', 'Đã cập nhật danh mục tour.');
    }

    public function destroy(TourCategory $tourCategory): RedirectResponse
    {
        $this->tourCategoryService->delete($tourCategory);

        return back()->with('success', 'Đã xóa danh mục tour.');
    }
}
