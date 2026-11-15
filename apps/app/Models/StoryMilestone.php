<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryMilestone extends Model
{
    protected $fillable = ['title', 'date', 'description', 'position', 'photo', 'is_visible'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }
}
