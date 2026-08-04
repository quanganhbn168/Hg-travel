<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Slug extends Model
{
    protected $fillable = ['slug', 'sluggable_type', 'sluggable_id', 'locale'];

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }
}
