<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilder extends Model
{
    protected $fillable = ['draft', 'published', 'revision', 'published_at'];

    protected function casts(): array
    {
        return ['draft' => 'array', 'published' => 'array', 'revision' => 'integer', 'published_at' => 'datetime'];
    }
}
