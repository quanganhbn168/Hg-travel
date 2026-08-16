<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BusinessSettings extends Settings
{
    public ?string $company_name;
    public ?string $legal_representative;
    public ?string $tax_code;

    public static function group(): string
    {
        return 'business';
    }
}
