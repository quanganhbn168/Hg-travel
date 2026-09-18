<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPromotionRequest;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotions) {}

    public function index(IndexPromotionRequest $request): View
    {
        return view('admin.promotions.index', [
            'promotions' => $this->promotions->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', $this->promotions->formContext());
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $promotion = $this->promotions->create($request->validated());

        return to_route('admin.promotions.edit', $promotion)
            ->with('success', 'Đã tạo ưu đãi.');
    }

    public function edit(Promotion $promotion): View
    {
        return view(
            'admin.promotions.form',
            $this->promotions->formContext($promotion),
        );
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $this->promotions->update($promotion, $request->validated());

        return back()->with('success', 'Đã cập nhật ưu đãi.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $this->promotions->delete($promotion);

        return to_route('admin.promotions.index')
            ->with('success', 'Đã xóa ưu đãi.');
    }
}
