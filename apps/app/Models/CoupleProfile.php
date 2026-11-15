<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoupleProfile extends Model
{
    protected $fillable = ['details', 'groom_photo', 'bride_photo', 'groom_bank_qr', 'bride_bank_qr'];

    public const IMAGE_FIELDS = ['groom_photo', 'bride_photo', 'groom_bank_qr', 'bride_bank_qr'];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }
}
