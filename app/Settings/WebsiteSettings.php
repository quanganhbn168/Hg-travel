<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{
    public string $site_name;
    public ?string $about_title;
    public ?string $about_paragraph_one;
    public ?string $about_paragraph_two;
    public ?string $custom_tour_title;
    public ?string $custom_tour_description;
    public string $impact_title;
    public string $impact_stat_one_number;
    public string $impact_stat_one_label;
    public string $impact_stat_two_number;
    public string $impact_stat_two_label;
    public string $impact_stat_three_number;
    public string $impact_stat_three_label;
    public string $impact_stat_four_number;
    public string $impact_stat_four_label;
    public ?string $partner_names;

    public static function group(): string
    {
        return 'website';
    }
}
