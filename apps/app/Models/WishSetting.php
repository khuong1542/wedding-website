<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishSetting extends Model
{
    protected $fillable = ['is_visible', 'allow_submissions', 'require_approval', 'display_limit', 'sort'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'allow_submissions' => 'boolean', 'require_approval' => 'boolean', 'display_limit' => 'integer'];
    }
}
