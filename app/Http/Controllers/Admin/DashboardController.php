<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourSchedule;
use App\Services\BookingService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalTours' => Tour::count(),
            'openTours' => Tour::query()->where('is_active', true)->where('booking_open', true)->where('status', 'published')->count(),
            'totalDestinations' => Destination::count(),
            'totalBookings' => Booking::count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'confirmedBookings' => Booking::whereIn('status', ['confirmed', 'completed'])->count(),
            'confirmedRevenue' => Booking::whereIn('status', ['confirmed', 'completed'])->sum('total_amount'),
            'bookingsThisMonth' => Booking::whereBetween('booked_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'recentBookings' => Booking::with('items.tour')->latest()->limit(8)->get(),
            'upcomingSchedules' => TourSchedule::with('tour:id,name,slug')
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->orderBy('departure_date')
                ->limit(6)
                ->get(),
            'bookingStatuses' => BookingService::STATUSES,
        ]);
    }
}
