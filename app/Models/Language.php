<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'flag', 'is_default', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget('active_languages'));
        static::deleted(fn () => cache()->forget('active_languages'));
    }

    public static function getActiveLanguages()
    {
        // Trả về collection mới theo request để tránh cache serialize Eloquent collection
        // trước khi class được autoload ở các process PHP khác nhau.
        return self::where('is_active', true)->orderBy('sort_order')->get();
    }
}
