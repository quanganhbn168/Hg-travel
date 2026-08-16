<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_paragraph_one' => ['nullable', 'string', 'max:2000'],
            'about_paragraph_two' => ['nullable', 'string', 'max:2000'],
            'custom_tour_title' => ['nullable', 'string', 'max:255'],
            'custom_tour_description' => ['nullable', 'string', 'max:1000'],
            'impact_title' => ['required', 'string', 'max:255'],
            'impact_stat_one_number' => ['required', 'string', 'max:50'],
            'impact_stat_one_label' => ['required', 'string', 'max:255'],
            'impact_stat_two_number' => ['required', 'string', 'max:50'],
            'impact_stat_two_label' => ['required', 'string', 'max:255'],
            'impact_stat_three_number' => ['required', 'string', 'max:50'],
            'impact_stat_three_label' => ['required', 'string', 'max:255'],
            'impact_stat_four_number' => ['required', 'string', 'max:50'],
            'impact_stat_four_label' => ['required', 'string', 'max:255'],
            'partner_names' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
