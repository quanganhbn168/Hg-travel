<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelMoment;
use App\Models\TravelMomentGroup;
use App\Services\MediaReferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TravelMomentController extends Controller
{
    public function index(): View
    {
        return view('admin.travel_moments.index', [
            'moments' => TravelMoment::with('group')->orderBy('sort_order')->latest('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.travel_moments.form', [
            'moment' => new TravelMoment(['is_active' => true]),
            'groups' => $this->groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $moment = TravelMoment::create($this->payload($request));

        return to_route('admin.travel-moments.edit', $moment)->with('success', 'Đã tạo khoảnh khắc.');
    }

    public function edit(TravelMoment $travelMoment): View
    {
        return view('admin.travel_moments.form', [
            'moment' => $travelMoment,
            'groups' => $this->groups(),
        ]);
    }

    public function update(Request $request, TravelMoment $travelMoment): RedirectResponse
    {
        $travelMoment->update($this->payload($request, $travelMoment));

        return back()->with('success', 'Đã cập nhật khoảnh khắc.');
    }

    public function destroy(TravelMoment $travelMoment): RedirectResponse
    {
        $travelMoment->delete();

        return to_route('admin.travel-moments.index')->with('success', 'Đã xóa khoảnh khắc.');
    }

    private function groups()
    {
        return TravelMomentGroup::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    private function payload(Request $request, ?TravelMoment $moment = null): array
    {
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:travel_moment_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('travel_moments', 'slug')->ignore($moment)],
            'image_url' => [$moment ? 'nullable' : 'required', 'string', 'max:4096'],
            'image_url_remove' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $image = app(MediaReferenceService::class)->field($data, 'image_url', $moment?->image_url);
        if (blank($image)) {
            throw ValidationException::withMessages(['image_url' => 'Khoảnh khắc cần có ảnh. Hãy chọn ảnh thay thế hoặc xóa bản ghi khoảnh khắc.']);
        }

        return [
            'group_id' => (int) $data['group_id'],
            'title' => trim($data['title']),
            'slug' => filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'image_url' => $image,
            'alt_text' => $data['alt_text'] ?? $data['title'],
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
