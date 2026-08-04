<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTourRequest;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Models\Tour;
use App\Services\TourService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TourController extends Controller
{
    public function __construct(private readonly TourService $tourService) {}

    public function index(IndexTourRequest $request): View
    {
        return view('admin.tours.index', [
            'tours' => $this->tourService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.tours.create', $this->tourService->formContext());
    }

    public function store(StoreTourRequest $request): RedirectResponse
    {
        $tour = $this->tourService->create($request->validated());

        return redirect()->route('admin.tours.edit', $tour)
            ->with('success', 'Đã tạo tour.');
    }

    public function edit(Tour $tour): View
    {
        return view('admin.tours.edit', $this->tourService->formContext($tour));
    }

    public function update(UpdateTourRequest $request, Tour $tour): RedirectResponse
    {
        $this->tourService->update($tour, $request->validated());

        return back()->with('success', 'Đã cập nhật tour.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $this->tourService->delete($tour);

        return back()->with('success', 'Đã xóa tour.');
    }
}
