<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.company_name', null);
        $this->migrator->add('general.contact_phone_secondary', null);
        $this->migrator->add('general.contact_email_secondary', null);
        $this->migrator->add('general.contact_email_tertiary', null);
        $this->migrator->add('general.whatsapp_url', null);
    }

    public function down(): void
    {
        foreach (['company_name', 'contact_phone_secondary', 'contact_email_secondary', 'contact_email_tertiary', 'whatsapp_url'] as $name) {
            $this->migrator->delete("general.{$name}");
        }
    }
};
