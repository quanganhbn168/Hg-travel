<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTestimonialRequest;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Models\Testimonial;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialService $testimonials) {}

    public function index(IndexTestimonialRequest $request): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => $this->testimonials->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.form', $this->testimonials->formContext());
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $testimonial = $this->testimonials->save($request->validated());

        return to_route('admin.testimonials.edit', $testimonial)
            ->with('success', 'Đã tạo cảm nhận.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view(
            'admin.testimonials.form',
            $this->testimonials->formContext($testimonial),
        );
    }

    public function update(StoreTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->testimonials->save($request->validated(), $testimonial);

        return back()->with('success', 'Đã cập nhật cảm nhận.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->testimonials->delete($testimonial);

        return to_route('admin.testimonials.index')
            ->with('success', 'Đã xóa cảm nhận.');
    }
}
