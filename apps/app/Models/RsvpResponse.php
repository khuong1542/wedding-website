<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RsvpResponse extends Model
{
    public const STATUSES = ['attending', 'declined', 'pending'];

    public const GROUPS = ['family', 'friends', 'colleagues', 'other'];

    protected $fillable = ['name', 'email', 'phone', 'group', 'status', 'attendees', 'notes', 'responded_at'];

    protected function casts(): array
    {
        return ['attendees' => 'integer', 'responded_at' => 'datetime'];
    }
}
