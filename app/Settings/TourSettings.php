<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TourSettings extends Settings
{
    public bool $show_schedules;
    public bool $show_seat_availability;
    public string $schedule_note;

    public static function group(): string
    {
        return 'tour';
    }
}
