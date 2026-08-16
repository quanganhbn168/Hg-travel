<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View { return view('admin.promotions.index', ['promotions' => Promotion::withCount('tours')->latest()->paginate(20)]); }
    public function create(): View { return view('admin.promotions.form', ['promotion' => new Promotion(['discount_type' => 'percentage', 'is_active' => true]), 'tours' => $this->tours()]); }
    public function store(Request $request): RedirectResponse { $promotion = Promotion::create($this->payload($request)); $promotion->tours()->sync($request->input('tour_ids', [])); return to_route('admin.promotions.edit', $promotion)->with('success', 'Đã tạo ưu đãi.'); }
    public function edit(Promotion $promotion): View { return view('admin.promotions.form', ['promotion' => $promotion->load('tours'), 'tours' => $this->tours()]); }
    public function update(Request $request, Promotion $promotion): RedirectResponse { $promotion->update($this->payload($request)); $promotion->tours()->sync($request->input('tour_ids', [])); return back()->with('success', 'Đã cập nhật ưu đãi.'); }
    public function destroy(Promotion $promotion): RedirectResponse { $promotion->delete(); return to_route('admin.promotions.index')->with('success', 'Đã xóa ưu đãi.'); }
    private function tours() { return Tour::orderBy('name')->get(['id','name','code']); }
    private function payload(Request $request): array { $data = $request->validate(['name' => ['required','string','max:255'], 'discount_type' => ['required','in:percentage,fixed'], 'discount_value' => ['required','numeric','min:0'], 'starts_at' => ['required','date'], 'ends_at' => ['required','date','after_or_equal:starts_at'], 'usage_limit' => ['nullable','integer','min:1'], 'is_active' => ['nullable','boolean'], 'tour_ids' => ['nullable','array'], 'tour_ids.*' => ['integer','exists:tours,id']]); return $data + ['is_active' => $request->boolean('is_active')]; }
}
