<?php

namespace App\Repositories\Admin;

use App\Models\CoupleProfile;

class CoupleRepository
{
    public function find(): ?CoupleProfile
    {
        return CoupleProfile::find(1);
    }

    public function findOrFail(): CoupleProfile
    {
        return CoupleProfile::findOrFail(1);
    }

    public function lock(): CoupleProfile
    {
        CoupleProfile::query()->insertOrIgnore(['id' => 1, 'details' => '{}', 'created_at' => now(), 'updated_at' => now()]);

        return CoupleProfile::query()->lockForUpdate()->findOrFail(1);
    }

    public function save(CoupleProfile $profile): void
    {
        $profile->save();
    }
}
