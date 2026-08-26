<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    protected $fillable = ['key','hero_title','hero_intro','letter_title','letter_content','story_title','story_content','vision','mission','markets_eyebrow','markets_title','markets_intro','profile_content','core_values','markets','commitments','audiences','ceo_name','ceo_bio','deputy_name','deputy_bio','background_image','hero_image','story_image','seo_title','seo_description'];
    protected function casts(): array { return ['profile_content' => 'array', 'core_values' => 'array', 'markets' => 'array', 'commitments' => 'array', 'audiences' => 'array']; }
}
