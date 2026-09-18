<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexSliderRequest;
use App\Http\Requests\Admin\StoreSliderItemRequest;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderItemRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Services\SliderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function __construct(private readonly SliderService $sliders) {}

    public function index(IndexSliderRequest $request): View
    {
        return view('admin.sliders.index', [
            'sliders' => $this->sliders->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.sliders.form', $this->sliders->formContext());
    }

    public function store(StoreSliderRequest $request): RedirectResponse
    {
        $slider = $this->sliders->create($request->validated());

        return to_route('admin.sliders.edit', $slider)
            ->with('success', 'Đã tạo slider.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.form', $this->sliders->formContext($slider));
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $this->sliders->update($slider, $request->validated());

        return back()->with('success', 'Đã cập nhật slider.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $this->sliders->delete($slider);

        return to_route('admin.sliders.index')
            ->with('success', 'Đã xóa slider.');
    }

    public function editItem(Slider $slider, SliderItem $item): View
    {
        $this->sliders->ensureItemBelongsToSlider($slider, $item);

        return view('admin.sliders.item-form', compact('slider', 'item'));
    }

    public function storeItem(StoreSliderItemRequest $request, Slider $slider): RedirectResponse
    {
        $this->sliders->addItem($slider, $request->validated());

        return back()->with('success', 'Đã thêm slide.');
    }

    public function updateItem(
        UpdateSliderItemRequest $request,
        Slider $slider,
        SliderItem $item,
    ): RedirectResponse {
        $this->sliders->updateItem($slider, $item, $request->validated());

        return to_route('admin.sliders.items.edit', [$slider, $item])
            ->with('success', 'Đã cập nhật slide.');
    }

    public function destroyItem(Slider $slider, SliderItem $item): RedirectResponse
    {
        $this->sliders->deleteItem($slider, $item);

        return back()->with('success', 'Đã xóa slide.');
    }
}
