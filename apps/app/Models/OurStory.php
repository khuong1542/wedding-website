<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OurStory extends Model
{
    protected $fillable = ['title', 'introduction'];

    public function milestones(): HasMany
    {
        return $this->hasMany(StoryMilestone::class)->orderBy('position')->orderBy('id');
    }
}
