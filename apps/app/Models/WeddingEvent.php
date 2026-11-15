<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingEvent extends Model
{
    public const TYPES = ['ceremony', 'reception', 'engagement', 'bachelor', 'preparation', 'other'];

    protected $fillable = ['side', 'title', 'type', 'date', 'start_time', 'end_time', 'venue', 'map_url', 'description', 'photo', 'is_visible'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }
}
