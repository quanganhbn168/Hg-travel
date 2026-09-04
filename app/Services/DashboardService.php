<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Support\Collection;

class DashboardService
{
    public function data(): array
    {
        $trendStart = now()->subDays(13)->startOfDay();
        $trendEnd = now()->endOfDay();
        $trendBookings = $this->bookingsBetween($trendStart, $trendEnd)->get([
            'status',
            'total_amount',
            'booked_at',
            'created_at',
        ]);
        $bookingTrend = $this->bookingTrend($trendBookings, $trendStart);
        $bookingStatusBreakdown = $this->statusBreakdown();
        $paymentStatusBreakdown = $this->paymentBreakdown();
        $upcomingScheduleStats = TourSchedule::query()
            ->where('status', 'open')
            ->whereDate('departure_date', '>=', today())
            ->get(['seats_total', 'seats_reserved']);
        $capacity = $upcomingScheduleStats->sum(fn (TourSchedule $schedule): int => max(0, (int) $schedule->seats_total));
        $reserved = $upcomingScheduleStats->sum(fn (TourSchedule $schedule): int => max(0, (int) $schedule->seats_reserved));

        return [
            'totalTours' => Tour::count(),
            'openTours' => Tour::query()
                ->where('is_active', true)
                ->where('booking_open', true)
                ->where('status', 'published')
                ->count(),
            'totalBookings' => Booking::count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'unpaidBookings' => Booking::where('payment_status', 'unpaid')
                ->whereNotIn('status', ['cancelled'])
                ->count(),
            'confirmedBookings' => Booking::whereIn('status', ['confirmed', 'completed'])->count(),
            'confirmedRevenue' => Booking::whereIn('status', ['confirmed', 'completed'])->sum('total_amount'),
            'bookingsThisMonth' => $this->bookingsBetween(now()->startOfMonth(), now()->endOfMonth())->count(),
            'recentBookings' => Booking::with('items.tour')->latest()->limit(8)->get(),
            'upcomingSchedules' => TourSchedule::with('tour:id,name,slug')
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->orderBy('departure_date')
                ->limit(6)
                ->get(),
            'openScheduleCount' => $upcomingScheduleStats->count(),
            'occupancyRate' => $capacity > 0 ? min(100, (int) round($reserved / $capacity * 100)) : null,
            'bookingTrend' => $bookingTrend,
            'bookingTrendTotal' => array_sum(array_column($bookingTrend, 'count')),
            'bookingStatuses' => BookingService::STATUSES,
            'paymentStatuses' => BookingService::PAYMENT_STATUSES,
            'bookingStatusBreakdown' => $bookingStatusBreakdown,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,
        ];
    }

    private function bookingsBetween($start, $end)
    {
        return Booking::query()->where(function ($query) use ($start, $end): void {
            $query
                ->whereBetween('booked_at', [$start, $end])
                ->orWhere(function ($fallback) use ($start, $end): void {
                    $fallback->whereNull('booked_at')->whereBetween('created_at', [$start, $end]);
                });
        });
    }

    private function bookingTrend(Collection $bookings, $trendStart): array
    {
        $trend = collect(range(0, 13))->map(function (int $offset) use ($bookings, $trendStart): array {
            $date = $trendStart->copy()->addDays($offset);
            $dayBookings = $bookings->filter(function (Booking $booking) use ($date): bool {
                $bookedAt = $booking->booked_at ?: $booking->created_at;

                return $bookedAt?->isSameDay($date) ?? false;
            });

            return [
                'label' => $date->format('d/m'),
                'count' => $dayBookings->count(),
                'revenue' => (float) $dayBookings
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->sum('total_amount'),
            ];
        });
        $maxCount = max(1, (int) $trend->max('count'));

        return $trend->map(fn (array $day): array => [
            ...$day,
            'height' => $day['count'] > 0 ? max(10, (int) round($day['count'] / $maxCount * 100)) : 0,
        ])->all();
    }

    private function statusBreakdown(): array
    {
        $counts = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $total = max(1, (int) $counts->sum());

        return collect(BookingService::STATUSES)->map(function (string $label, string $status) use ($counts, $total): array {
            $count = (int) ($counts[$status] ?? 0);

            return [
                'key' => $status,
                'label' => $label,
                'count' => $count,
                'percentage' => (int) round($count / $total * 100),
                'color' => match ($status) {
                    'pending' => 'warning',
                    'confirmed' => 'primary',
                    'cancelled' => 'secondary',
                    'completed' => 'success',
                    default => 'info',
                },
            ];
        })->values()->all();
    }

    private function paymentBreakdown(): array
    {
        $counts = Booking::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        return collect(BookingService::PAYMENT_STATUSES)->map(function (string $label, string $status) use ($counts): array {
            return [
                'key' => $status,
                'label' => $label,
                'count' => (int) ($counts[$status] ?? 0),
            ];
        })->values()->all();
    }
}
