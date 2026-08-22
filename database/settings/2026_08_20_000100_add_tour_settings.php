<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'tour.show_schedules' => true,
            'tour.show_seat_availability' => true,
            'tour.schedule_note' => 'Giá, lịch và số chỗ được cập nhật theo từng đợt khởi hành.',
        ] as $property => $value) {
            if (! $this->migrator->exists($property)) {
                $this->migrator->add($property, $value);
            }
        }
    }

    public function down(): void
    {
        foreach (['show_schedules', 'show_seat_availability', 'schedule_note'] as $property) {
            $this->migrator->deleteIfExists('tour.'.$property);
        }
    }
};
