<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View { return view('admin.coupons.index', ['coupons' => Coupon::withCount('tours')->latest()->paginate(20)]); }
    public function create(): View { return view('admin.coupons.form', ['coupon' => new Coupon(['discount_type' => 'percentage', 'is_active' => true]), 'tours' => $this->tours()]); }
    public function store(Request $request): RedirectResponse { $coupon = Coupon::create($this->payload($request, true)); $coupon->tours()->sync($request->input('tour_ids', [])); return to_route('admin.coupons.edit', $coupon)->with('success', 'Đã tạo mã giảm giá.'); }
    public function edit(Coupon $coupon): View { return view('admin.coupons.form', ['coupon' => $coupon->load('tours'), 'tours' => $this->tours()]); }
    public function update(Request $request, Coupon $coupon): RedirectResponse { $coupon->update($this->payload($request, false, $coupon)); $coupon->tours()->sync($request->input('tour_ids', [])); return back()->with('success', 'Đã cập nhật mã giảm giá.'); }
    public function destroy(Coupon $coupon): RedirectResponse { $coupon->delete(); return to_route('admin.coupons.index')->with('success', 'Đã xóa mã giảm giá.'); }
    private function tours() { return Tour::orderBy('name')->get(['id','name','code']); }
    private function payload(Request $request, bool $creating = false, ?Coupon $coupon = null): array { $data = $request->validate(['code' => ['required','string','max:50', 'unique:coupons,code'.($creating ? '' : ','.$coupon->id)], 'name' => ['required','string','max:255'], 'discount_type' => ['required','in:percentage,fixed'], 'discount_value' => ['required','numeric','min:0'], 'minimum_booking_amount' => ['nullable','numeric','min:0'], 'maximum_discount_amount' => ['nullable','numeric','min:0'], 'usage_limit' => ['nullable','integer','min:1'], 'starts_at' => ['nullable','date'], 'ends_at' => ['nullable','date','after_or_equal:starts_at'], 'is_active' => ['nullable','boolean'], 'tour_ids' => ['nullable','array'], 'tour_ids.*' => ['integer','exists:tours,id']]); return $data + ['code' => Str::upper(trim($data['code'])), 'is_active' => $request->boolean('is_active')]; }
}
