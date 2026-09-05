<?php

namespace App\Models;

use App\Traits\HasManagedImages;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasManagedImages;

    protected $fillable = ['customer_name', 'customer_title', 'content', 'rating', 'avatar_path', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
