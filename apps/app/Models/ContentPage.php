<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $fillable = ['title', 'slug', 'is_visible', 'photo', 'summary', 'seo_title', 'seo_description', 'canonical_url', 'sections', 'blade_template', 'groom_name', 'bride_name'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sections' => 'array'];
    }
}
