<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['tour_id', 'departure_date', 'return_date', 'seats_total', 'seats_reserved', 'price', 'sale_price', 'transport', 'source_seat_info_raw', 'source_seat_interpretation', 'source_sheet', 'source_row', 'source_date_raw', 'source_name_raw', 'status', 'notes'];
    protected function casts(): array { return ['departure_date' => 'date', 'return_date' => 'date', 'price' => 'decimal:2', 'sale_price' => 'decimal:2']; }
    public function tour(): BelongsTo { return $this->belongsTo(Tour::class); }
    public function bookingItems(): HasMany { return $this->hasMany(BookingItem::class); }

    public function seatsLeft(): ?int
    {
        $total = (int) $this->seats_total;

        return $total > 0 ? max(0, $total - (int) $this->seats_reserved) : null;
    }

    public function isAvailable(): bool
    {
        $seatsLeft = $this->seatsLeft();

        return $this->status === 'open' && ($seatsLeft === null || $seatsLeft > 0);
    }

    public function effectivePrice(): float
    {
        return (float) $this->sale_price > 0 ? (float) $this->sale_price : (float) $this->price;
    }

    public function slotLabel(): string
    {
        $seatsLeft = $this->seatsLeft();

        return $seatsLeft === null
            ? 'Đang cập nhật'
            : 'Còn '.$seatsLeft.'/'.(int) $this->seats_total.' chỗ';
    }
}
