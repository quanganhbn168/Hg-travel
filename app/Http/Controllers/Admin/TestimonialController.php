<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('sort_order')->latest('id')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['rating' => 5, 'is_active' => true])]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $testimonial = app(TestimonialService::class)->save($request->validated());

        return to_route('admin.testimonials.edit', $testimonial)->with('success', 'Đã tạo cảm nhận.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(StoreTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        app(TestimonialService::class)->save($request->validated(), $testimonial);

        return back()->with('success', 'Đã cập nhật cảm nhận.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return to_route('admin.testimonials.index')->with('success', 'Đã xóa cảm nhận.');
    }
}
