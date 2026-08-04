<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Du lịch');
        $this->migrator->add('general.contact_email', null);
        $this->migrator->add('general.contact_phone', null);
        $this->migrator->add('general.office_address', null);
        $this->migrator->add('general.seo_title', 'Du lịch');
        $this->migrator->add('general.seo_description', null);
        $this->migrator->add('general.seo_keywords', null);
        $this->migrator->add('general.logo_url', null);
        $this->migrator->add('general.favicon_url', null);
        $this->migrator->add('general.facebook_url', null);
        $this->migrator->add('general.instagram_url', null);
        $this->migrator->add('general.youtube_url', null);
        $this->migrator->add('general.zalo_url', null);
        $this->migrator->add('general.messenger_url', null);

        $this->migrator->add('media.media_allowed_extensions', 'jpg,jpeg,png,webp,gif,pdf,doc,docx');
        $this->migrator->add('media.media_max_size', 10);
    }

    public function down(): void
    {
        foreach ([
            'site_name', 'contact_email', 'contact_phone', 'office_address',
            'seo_title', 'seo_description', 'seo_keywords', 'logo_url',
            'favicon_url', 'facebook_url', 'instagram_url', 'youtube_url',
            'zalo_url', 'messenger_url',
        ] as $name) {
            $this->migrator->delete("general.{$name}");
        }

        $this->migrator->delete('media.media_allowed_extensions');
        $this->migrator->delete('media.media_max_size');
    }
};
