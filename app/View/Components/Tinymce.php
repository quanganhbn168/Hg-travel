<?php

namespace App\View\Components;

use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Tinymce extends Component
{
    public string $inputId;

    public array $translations;

    public $langs;

    public bool $showTabs;

    public function __construct(public string $name, public ?string $label = null, public mixed $value = null, public int $rows = 10, public bool $required = false, public bool $translatable = false, public ?string $id = null)
    {
        $this->inputId = $id ?: 'tinymce_'.Str::random(10);
        $this->translations = is_array($value) ? $value : (is_object($value) && method_exists($value, 'getTranslations') ? $value->getTranslations($name) : []);
        $this->langs = $translatable ? Language::getActiveLanguages() : collect();
        if ($translatable && $this->langs->isEmpty()) {
            $this->langs = collect([(object) ['code' => 'vi', 'name' => 'Tiếng Việt'], (object) ['code' => 'en', 'name' => 'English']]);
        }
        $this->showTabs = $translatable && $this->langs->count() > 1;
    }

    public function render()
    {
        return view('components.tinymce');
    }
}
