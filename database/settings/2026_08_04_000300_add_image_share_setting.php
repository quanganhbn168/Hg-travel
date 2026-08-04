<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.image_share_url', null);
    }

    public function down(): void
    {
        $this->migrator->delete('general.image_share_url');
    }
};
