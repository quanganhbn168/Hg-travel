<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexProductLineRequest;
use App\Http\Requests\Admin\StoreProductLineRequest;
use App\Http\Requests\Admin\UpdateProductLineRequest;
use App\Models\ProductLine;
use App\Services\ProductLineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductLineController extends Controller
{
    public function __construct(private readonly ProductLineService $productLineService) {}

    public function index(IndexProductLineRequest $request): View
    {
        return view('admin.product_lines.index', [
            'productLines' => $this->productLineService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.product_lines.create', $this->productLineService->formContext());
    }

    public function store(StoreProductLineRequest $request): RedirectResponse
    {
        $productLine = $this->productLineService->create($request->validated());

        return redirect()->route('admin.product-lines.edit', $productLine)->with('success', 'Đã tạo giải pháp du lịch.');
    }

    public function edit(ProductLine $productLine): View
    {
        return view('admin.product_lines.edit', $this->productLineService->formContext($productLine));
    }

    public function update(UpdateProductLineRequest $request, ProductLine $productLine): RedirectResponse
    {
        $this->productLineService->update($productLine, $request->validated());

        return back()->with('success', 'Đã cập nhật giải pháp du lịch.');
    }

    public function destroy(ProductLine $productLine): RedirectResponse
    {
        $this->productLineService->delete($productLine);

        return redirect()->route('admin.product-lines.index')->with('success', 'Đã xóa giải pháp du lịch.');
    }
}
