<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppearanceSetting extends Model
{
    protected $fillable = ['settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }
}
