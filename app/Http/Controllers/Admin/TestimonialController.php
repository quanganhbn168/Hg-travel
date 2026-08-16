<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View { return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->latest('id')->paginate(20)]); }
    public function create(): View { return view('admin.testimonials.form', ['testimonial' => new Testimonial(['rating' => 5, 'is_active' => true])]); }
    public function store(Request $request): RedirectResponse { $testimonial = Testimonial::create($this->payload($request)); return to_route('admin.testimonials.edit', $testimonial)->with('success', 'Đã tạo cảm nhận.'); }
    public function edit(Testimonial $testimonial): View { return view('admin.testimonials.form', compact('testimonial')); }
    public function update(Request $request, Testimonial $testimonial): RedirectResponse { $testimonial->update($this->payload($request)); return back()->with('success', 'Đã cập nhật cảm nhận.'); }
    public function destroy(Testimonial $testimonial): RedirectResponse { $testimonial->delete(); return to_route('admin.testimonials.index')->with('success', 'Đã xóa cảm nhận.'); }
    private function payload(Request $request): array { $data = $request->validate(['customer_name' => ['required','string','max:255'], 'customer_title' => ['nullable','string','max:255'], 'content' => ['required','string','max:5000'], 'rating' => ['required','integer','between:1,5'], 'avatar_path' => ['nullable','string','max:4096'], 'sort_order' => ['nullable','integer','min:0'], 'is_active' => ['nullable','boolean']]); return $data + ['sort_order' => (int) ($data['sort_order'] ?? 0), 'is_active' => $request->boolean('is_active')]; }
}
