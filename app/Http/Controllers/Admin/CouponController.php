<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexCouponRequest;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(IndexCouponRequest $request): View
    {
        return view('admin.coupons.index', [
            'coupons' => $this->coupons->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', $this->coupons->formContext());
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = $this->coupons->create($request->validated());

        return to_route('admin.coupons.edit', $coupon)
            ->with('success', 'Đã tạo mã giảm giá.');
    }

    public function edit(Coupon $coupon): View
    {
        return view(
            'admin.coupons.form',
            $this->coupons->formContext($coupon),
        );
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->coupons->update($coupon, $request->validated());

        return back()->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->coupons->delete($coupon);

        return to_route('admin.coupons.index')
            ->with('success', 'Đã xóa mã giảm giá.');
    }
}
