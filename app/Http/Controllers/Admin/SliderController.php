<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function index(): View
    {
        return view('admin.sliders.index', [
            'sliders' => Slider::withCount('items')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.sliders.form', [
            'slider' => new Slider(['is_active' => true]),
            'items' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $slider = Slider::create($this->payload($request, true));

        return to_route('admin.sliders.edit', $slider)->with('success', 'Đã tạo slider.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.form', [
            'slider' => $slider,
            'items' => $slider->items()->get(),
        ]);
    }

    public function update(Request $request, Slider $slider): RedirectResponse
    {
        $slider->update($this->payload($request, false, $slider));

        return back()->with('success', 'Đã cập nhật slider.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $slider->delete();

        return to_route('admin.sliders.index')->with('success', 'Đã xóa slider.');
    }

    public function editItem(Slider $slider, SliderItem $item): View
    {
        $this->ensureItemBelongsToSlider($slider, $item);

        return view('admin.sliders.item-form', compact('slider', 'item'));
    }

    public function storeItem(Request $request, Slider $slider): RedirectResponse
    {
        $slider->items()->create($this->itemPayload($request));

        return back()->with('success', 'Đã thêm slide.');
    }

    public function updateItem(Request $request, Slider $slider, SliderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToSlider($slider, $item);
        $item->update($this->itemPayload($request, $item));

        return to_route('admin.sliders.items.edit', [$slider, $item])->with('success', 'Đã cập nhật slide.');
    }

    public function destroyItem(Slider $slider, SliderItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToSlider($slider, $item);
        $item->delete();

        return back()->with('success', 'Đã xóa slide.');
    }

    private function payload(Request $request, bool $creating, ?Slider $slider = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $key = $creating ? Str::slug($data['key']) : (string) $slider?->key;

        if ($key === '') {
            throw ValidationException::withMessages(['key' => 'Key phải chứa ít nhất một ký tự hợp lệ.']);
        }

        $duplicate = Slider::query()
            ->where('key', $key)
            ->when($slider, fn ($query) => $query->whereKeyNot($slider->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['key' => 'Key này đã được sử dụng cho một slider khác.']);
        }

        return [
            'name' => trim($data['name']),
            'key' => $key,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function itemPayload(Request $request, ?SliderItem $item = null): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:2000'],
            'image_path' => [$item ? 'nullable' : 'required', 'string', 'max:4096'],
            'image_path_remove' => ['nullable', 'boolean'],
            'button_label' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = trim((string) ($data['image_path'] ?? ''));

        if ($item) {
            if ($request->boolean('image_path_remove') && $imagePath === '') {
                throw ValidationException::withMessages(['image_path' => 'Hãy chọn ảnh mới trước khi thay ảnh hiện tại.']);
            }

            $imagePath = $imagePath !== '' ? $imagePath : (string) $item->image_path;
        }

        return [
            'title' => filled($data['title'] ?? null) ? trim($data['title']) : null,
            'subtitle' => filled($data['subtitle'] ?? null) ? trim($data['subtitle']) : null,
            'image_path' => $imagePath,
            'button_label' => filled($data['button_label'] ?? null) ? trim($data['button_label']) : null,
            'button_url' => filled($data['button_url'] ?? null) ? trim($data['button_url']) : null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function ensureItemBelongsToSlider(Slider $slider, SliderItem $item): void
    {
        abort_unless((int) $item->slider_id === (int) $slider->id, 404);
    }
}
