<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'name', 'payload', 'locked'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'locked' => 'boolean'];
    }
}
