<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTravelMomentRequest;
use App\Http\Requests\Admin\StoreTravelMomentRequest;
use App\Http\Requests\Admin\UpdateTravelMomentRequest;
use App\Models\TravelMoment;
use App\Services\TravelMomentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TravelMomentController extends Controller
{
    public function __construct(private readonly TravelMomentService $moments) {}

    public function index(IndexTravelMomentRequest $request): View
    {
        return view(
            'admin.travel_moments.index',
            $this->moments->indexContext($request->validated()),
        );
    }

    public function create(): View
    {
        return view('admin.travel_moments.form', $this->moments->formContext());
    }

    public function store(StoreTravelMomentRequest $request): RedirectResponse
    {
        $moment = $this->moments->create($request->validated());

        return to_route('admin.travel-moments.edit', $moment)
            ->with('success', 'Đã tạo khoảnh khắc.');
    }

    public function edit(TravelMoment $travelMoment): View
    {
        return view(
            'admin.travel_moments.form',
            $this->moments->formContext($travelMoment),
        );
    }

    public function update(UpdateTravelMomentRequest $request, TravelMoment $travelMoment): RedirectResponse
    {
        $this->moments->update($travelMoment, $request->validated());

        return back()->with('success', 'Đã cập nhật khoảnh khắc.');
    }

    public function destroy(TravelMoment $travelMoment): RedirectResponse
    {
        $this->moments->delete($travelMoment);

        return to_route('admin.travel-moments.index')
            ->with('success', 'Đã xóa khoảnh khắc.');
    }
}
