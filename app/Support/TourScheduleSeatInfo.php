<?php

namespace App\Support;

final class TourScheduleSeatInfo
{
    /**
     * HG TRIP source convention: x/y means x guests are booked from y total seats.
     *
     * @return array{seats_total: int, seats_reserved: int, interpretation: string}|null
     */
    public static function parseBookedTotal(?string $raw): ?array
    {
        $raw = trim((string) $raw);

        if (! preg_match("/^\\s*(\\d+)\\s*\\/\\s*(\\d+)\\s*'?\\s*$/", $raw, $matches)) {
            return null;
        }

        $total = (int) $matches[2];

        if ($total < 1) {
            return null;
        }

        return [
            'seats_total' => $total,
            'seats_reserved' => min((int) $matches[1], $total),
            'interpretation' => 'Đã đặt/Tổng số chỗ (x/y)',
        ];
    }
}
