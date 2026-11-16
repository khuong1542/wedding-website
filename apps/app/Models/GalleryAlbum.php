<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    protected $fillable = ['title', 'description', 'is_visible', 'position'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'position' => 'integer'];
    }

    public function media(): HasMany
    {
        return $this->hasMany(GalleryMedia::class, 'album_id');
    }
}
