<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function index(): View { return view('admin.sliders.index', ['sliders' => Slider::withCount('items')->orderBy('name')->paginate(20)]); }
    public function create(): View { return view('admin.sliders.form', ['slider' => new Slider(['is_active' => true]), 'items' => collect()]); }
    public function store(Request $request): RedirectResponse { $slider = Slider::create($this->payload($request, true)); return to_route('admin.sliders.edit', $slider)->with('success', 'Đã tạo slider.'); }
    public function edit(Slider $slider): View { return view('admin.sliders.form', ['slider' => $slider, 'items' => $slider->items()->get()]); }
    public function update(Request $request, Slider $slider): RedirectResponse { $slider->update($this->payload($request)); return back()->with('success', 'Đã cập nhật slider.'); }
    public function destroy(Slider $slider): RedirectResponse { $slider->delete(); return to_route('admin.sliders.index')->with('success', 'Đã xóa slider.'); }
    public function storeItem(Request $request, Slider $slider): RedirectResponse { $slider->items()->create($this->itemPayload($request)); return back()->with('success', 'Đã thêm slide.'); }
    public function updateItem(Request $request, Slider $slider, SliderItem $item): RedirectResponse { abort_unless($item->slider_id === $slider->id, 404); $item->update($this->itemPayload($request)); return back()->with('success', 'Đã cập nhật slide.'); }
    public function destroyItem(Slider $slider, SliderItem $item): RedirectResponse { abort_unless($item->slider_id === $slider->id, 404); $item->delete(); return back()->with('success', 'Đã xóa slide.'); }
    private function payload(Request $request, bool $creating = false): array { $data = $request->validate(['name' => ['required','string','max:255'], 'key' => ['required','string','max:100', $creating ? 'unique:sliders,key' : 'unique:sliders,key,'.$request->route('slider')->id], 'is_active' => ['nullable','boolean']]); return ['name' => trim($data['name']), 'key' => Str::slug($data['key']), 'is_active' => $request->boolean('is_active')]; }
    private function itemPayload(Request $request): array { $data = $request->validate(['title' => ['nullable','string','max:255'], 'subtitle' => ['nullable','string','max:2000'], 'image_path' => ['required','string','max:4096'], 'button_label' => ['nullable','string','max:100'], 'button_url' => ['nullable','string','max:2048'], 'sort_order' => ['nullable','integer','min:0'], 'is_active' => ['nullable','boolean']]); return $data + ['sort_order' => (int) ($data['sort_order'] ?? 0), 'is_active' => $request->boolean('is_active')]; }
}
