<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexDestinationRequest;
use App\Http\Requests\Admin\StoreDestinationRequest;
use App\Http\Requests\Admin\UpdateDestinationRequest;
use App\Models\Destination;
use App\Services\DestinationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function __construct(private readonly DestinationService $destinationService) {}

    public function index(IndexDestinationRequest $request): View
    {
        return view('admin.destinations.index', [
            'destinations' => $this->destinationService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.destinations.create', $this->destinationService->formContext());
    }

    public function store(StoreDestinationRequest $request): RedirectResponse
    {
        $destination = $this->destinationService->create($request->validated());

        return redirect()->route('admin.destinations.edit', $destination)
            ->with('success', 'Đã tạo điểm đến.');
    }

    public function edit(Destination $destination): View
    {
        return view('admin.destinations.edit', $this->destinationService->formContext($destination));
    }

    public function update(UpdateDestinationRequest $request, Destination $destination): RedirectResponse
    {
        $this->destinationService->update($destination, $request->validated());

        return back()->with('success', 'Đã cập nhật điểm đến.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        abort_if($destination->is_system, 404);
        $this->destinationService->delete($destination);

        return back()->with('success', 'Đã xóa điểm đến.');
    }
}
