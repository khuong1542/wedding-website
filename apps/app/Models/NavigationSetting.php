<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationSetting extends Model
{
    protected $fillable = ['menu', 'footer_links', 'footer', 'display', 'logo'];

    protected function casts(): array
    {
        return ['menu' => 'array', 'footer_links' => 'array', 'footer' => 'array', 'display' => 'array'];
    }
}
