<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    public const STATUSES = ['published', 'pending', 'hidden'];

    protected $fillable = ['name', 'email', 'message', 'status'];
}
