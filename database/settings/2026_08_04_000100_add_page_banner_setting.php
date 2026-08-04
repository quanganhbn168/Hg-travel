<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.page_banner_url', null);
    }

    public function down(): void
    {
        $this->migrator->delete('general.page_banner_url');
    }
};
