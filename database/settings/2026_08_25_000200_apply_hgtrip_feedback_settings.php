<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    private const LEGAL_NAME = 'CÔNG TY TNHH DỊCH VỤ DU LỊCH VÀ THƯƠNG MẠI HG';

    private const LICENSE_NUMBER = '01-0014/2026/SDL-GP LHQT';

    private const BRAND_STATEMENT = 'Chúng tôi mong muốn mỗi lần khách hàng lựa chọn HG TRIP không chỉ là một lần đặt dịch vụ, mà là một lần bắt đầu một hành trình mà ở đó họ có thể hoàn toàn an tâm tận hưởng, khám phá và tạo nên những kỷ niệm của riêng mình.';

    private const TAX_CODE = '0111549317';

    private const QUICK_EMAIL = 'hgtrip.ltdcompany@gmail.com';

    private const PREVIOUS_EMAIL = 'giangnh@hgtrip.biz';

    public function up(): void
    {
        foreach ([
            'business.travel_license_number' => self::LICENSE_NUMBER,
            'business.brand_statement' => self::BRAND_STATEMENT,
        ] as $property => $value) {
            if (! $this->migrator->exists($property)) {
                $this->migrator->add($property, $value);
            }
        }

        $this->updateWhen('business.travel_license_number', static fn ($value): bool => blank($value), self::LICENSE_NUMBER);
        $this->updateWhen('business.brand_statement', static fn ($value): bool => blank($value), self::BRAND_STATEMENT);
        $this->updateWhen('business.company_name', static fn ($value): bool => blank($value), self::LEGAL_NAME);
        $this->updateWhen('business.tax_code', static fn ($value): bool => blank($value), self::TAX_CODE);
        $this->updateWhen('media.logo_url', static fn ($value): bool => $value === 'images/logo-hg.png', 'images/logo-hgtrip.png');
        $this->updateWhen('contact.zalo_url', static fn ($value): bool => blank($value), 'https://zalo.me/0906066036');
        $this->updateWhen('contact.whatsapp_url', static fn ($value): bool => blank($value), 'https://wa.me/84906066036');
        $this->updateWhen('contact.contact_email', static fn ($value): bool => $value === self::PREVIOUS_EMAIL, self::QUICK_EMAIL);
        $this->updateWhen('contact.contact_email_tertiary', static fn ($value): bool => $value === self::QUICK_EMAIL, self::PREVIOUS_EMAIL);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('business.travel_license_number');
        $this->migrator->deleteIfExists('business.brand_statement');

        $this->updateWhen('business.tax_code', static fn ($value): bool => $value === self::TAX_CODE, null);
        $this->updateWhen('media.logo_url', static fn ($value): bool => $value === 'images/logo-hgtrip.png', 'images/logo-hg.png');
        $this->updateWhen('contact.zalo_url', static fn ($value): bool => $value === 'https://zalo.me/0906066036', null);
        $this->updateWhen('contact.whatsapp_url', static fn ($value): bool => $value === 'https://wa.me/84906066036', null);
        $this->updateWhen('contact.contact_email', static fn ($value): bool => $value === self::QUICK_EMAIL, self::PREVIOUS_EMAIL);
        $this->updateWhen('contact.contact_email_tertiary', static fn ($value): bool => $value === self::PREVIOUS_EMAIL, self::QUICK_EMAIL);
    }

    private function updateWhen(string $property, callable $condition, mixed $replacement): void
    {
        if (! $this->migrator->exists($property)) {
            return;
        }

        $this->migrator->update($property, static fn ($value) => $condition($value) ? $replacement : $value);
    }
};
