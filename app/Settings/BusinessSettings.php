<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BusinessSettings extends Settings
{
    public ?string $company_name;
    public ?string $legal_representative;
    public ?string $tax_code;
    public ?string $travel_license_number;
    public ?string $brand_statement;

    public static function group(): string
    {
        return 'business';
    }
}
