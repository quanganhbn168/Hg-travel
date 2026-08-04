<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Booking\IndexBookingRequest;
use App\Http\Requests\Admin\Booking\StoreBookingRequest;
use App\Http\Requests\Admin\Booking\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(IndexBookingRequest $request): View
    {
        return view('admin.bookings.index', [
            'bookings' => $this->bookingService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return view('admin.bookings.create', $this->bookingService->formContext());
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->create($request->validated());

        return redirect()->route('admin.bookings.edit', $booking)
            ->with('success', 'Đã tạo booking.');
    }

    public function edit(Booking $booking): View
    {
        return view('admin.bookings.edit', [
            'booking' => $booking->load('items.tour'),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookingService->update($booking, $request->validated());

        return back()->with('success', 'Đã cập nhật booking.');
    }
}
