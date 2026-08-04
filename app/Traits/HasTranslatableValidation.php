<?php

namespace App\Traits;

use App\Models\Language;

trait HasTranslatableValidation
{
    protected function applyTranslatableRules(array $rules, array $translatableRules, bool $nullableForOthers = true): array
    {
        $languages = Language::getActiveLanguages();
        if ($languages->isEmpty()) {
            $languages = collect([
                (object) ['code' => 'vi', 'is_default' => true],
                (object) ['code' => 'en', 'is_default' => false],
            ]);
        }

        $default = $languages->firstWhere('is_default', true)?->code ?? 'vi';
        foreach ($languages as $language) {
            foreach ($translatableRules as $field => $fieldRules) {
                $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
                if ($nullableForOthers && $language->code !== $default) {
                    $fieldRules = array_map(fn ($rule) => $rule === 'required' ? 'nullable' : $rule, $fieldRules);
                    $fieldRules[] = 'nullable';
                }
                $rules[$field.'.'.$language->code] = array_values(array_unique($fieldRules));
            }
        }

        return $rules;
    }

    protected function applyTranslatableAttributes(array $attributes, array $fieldLabels): array
    {
        $languages = Language::getActiveLanguages();
        if ($languages->isEmpty()) {
            $languages = collect([
                (object) ['code' => 'vi', 'name' => 'Tiếng Việt'],
                (object) ['code' => 'en', 'name' => 'English'],
            ]);
        }

        foreach ($languages as $language) {
            foreach ($fieldLabels as $field => $label) {
                $attributes[$field.'.'.$language->code] = $label.' ('.$language->name.')';
            }
        }

        return $attributes;
    }
}
