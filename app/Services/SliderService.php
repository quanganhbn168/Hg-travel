<?php

namespace App\Services;

use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SliderService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Slider::query()
            ->withCount('items')
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%")
                )
            )
            ->when(
                ($filters['status'] ?? null) === 'active',
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                ($filters['status'] ?? null) === 'inactive',
                fn ($query) => $query->where('is_active', false)
            )
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function formContext(?Slider $slider = null): array
    {
        $slider ??= new Slider(['is_active' => true]);

        return [
            'slider' => $slider,
            'items' => $slider->exists
                ? $slider->items()->get()
                : collect(),
        ];
    }

    public function create(array $data): Slider
    {
        return Slider::create($this->sliderPayload($data));
    }

    public function update(Slider $slider, array $data): void
    {
        $slider->update([
            'name' => trim($data['name']),
            'key' => $slider->key,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);
    }

    public function delete(Slider $slider): void
    {
        $slider->delete();
    }

    public function addItem(Slider $slider, array $data): SliderItem
    {
        return $slider->items()->create($this->itemPayload($data));
    }

    public function updateItem(Slider $slider, SliderItem $item, array $data): void
    {
        $this->ensureItemBelongsToSlider($slider, $item);

        $item->update($this->itemPayload($data, $item));
    }

    public function deleteItem(Slider $slider, SliderItem $item): void
    {
        $this->ensureItemBelongsToSlider($slider, $item);
        $item->delete();
    }

    public function ensureItemBelongsToSlider(Slider $slider, SliderItem $item): void
    {
        abort_unless((int) $item->slider_id === (int) $slider->id, 404);
    }

    private function sliderPayload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'key' => $data['key'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function itemPayload(array $data, ?SliderItem $item = null): array
    {
        $imagePath = trim((string) ($data['image_path'] ?? ''));

        if ($item) {
            if ((bool) ($data['image_path_remove'] ?? false) && $imagePath === '') {
                throw ValidationException::withMessages([
                    'image_path' => 'Hãy chọn ảnh mới trước khi thay ảnh hiện tại.',
                ]);
            }

            $imagePath = $imagePath !== ''
                ? $imagePath
                : (string) $item->image_path;
        }

        if ($imagePath === '') {
            throw ValidationException::withMessages([
                'image_path' => 'Slide cần có ảnh.',
            ]);
        }

        return [
            'title' => filled($data['title'] ?? null)
                ? trim($data['title'])
                : null,
            'subtitle' => filled($data['subtitle'] ?? null)
                ? trim($data['subtitle'])
                : null,
            'image_path' => $imagePath,
            'button_label' => filled($data['button_label'] ?? null)
                ? trim($data['button_label'])
                : null,
            'button_url' => filled($data['button_url'] ?? null)
                ? trim($data['button_url'])
                : null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
