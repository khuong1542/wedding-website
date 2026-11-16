<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public const STATUSES = ['active', 'draft', 'archived'];

    protected $fillable = ['title', 'status', 'template', 'description', 'photo', 'groom_name', 'bride_name', 'date', 'time', 'venue', 'message', 'accent', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
