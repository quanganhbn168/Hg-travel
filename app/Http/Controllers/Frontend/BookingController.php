<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreBookingRequest;
use App\Models\Tour;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, BookingService $bookingService): View
    {
        $selectedTour = Tour::query()
            ->where('slug', (string) $request->query('tour'))
            ->where('is_active', true)
            ->where('booking_open', true)
            ->where('status', 'published')
            ->first();
        $selectedSchedule = $selectedTour && $request->filled('schedule')
            ? $bookingService->findPublicSchedule($selectedTour, (int) $request->query('schedule'))
            : null;

        return view('frontend.booking.create', $bookingService->publicFormContext($selectedTour, $selectedSchedule));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService): RedirectResponse
    {
        $booking = $bookingService->createPublic($request->validated());

        if ($request->input('source') === 'tour_detail') {
            $tour = $booking->load('items.tour')->items->first()?->tour;

            if ($tour) {
                return redirect(route('tours.show', $tour).'#tour-booking-modal')
                    ->with('booking_success', 'Đã giữ chỗ theo yêu cầu '.$booking->booking_code.'. HG sẽ liên hệ xác nhận sớm nhất.');
            }
        }

        return to_route('booking.create')->with('success', 'Đã ghi nhận yêu cầu đặt tour '.$booking->booking_code.'. HG sẽ liên hệ xác nhận lịch và chi phí với anh/chị.');
    }
}
