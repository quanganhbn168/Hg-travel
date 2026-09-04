<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DestinationAlias extends Model
{
    use HasFactory;

    protected $fillable = ['destination_id', 'alias', 'normalized_alias', 'locale', 'source'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public static function normalize(string $value): string
    {
        $ascii = Str::upper(Str::ascii($value));

        return trim((string) preg_replace('/[^\pL\pN]+/u', ' ', $ascii));
    }
}
