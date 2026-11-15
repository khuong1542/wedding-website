<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingRegistry extends Model
{
    protected $fillable = ['settings', 'gifts'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'gifts' => 'array'];
    }
}
