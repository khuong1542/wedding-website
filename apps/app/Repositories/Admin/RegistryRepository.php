<?php

namespace App\Repositories\Admin;

use App\Models\WeddingRegistry;

class RegistryRepository
{
    public function find(): ?WeddingRegistry
    {
        return WeddingRegistry::find(1);
    }

    public function lock(): WeddingRegistry
    {
        WeddingRegistry::query()->insertOrIgnore(['id' => 1, 'settings' => '{}', 'gifts' => '[]', 'created_at' => now(), 'updated_at' => now()]);

        return WeddingRegistry::query()->lockForUpdate()->findOrFail(1);
    }

    public function save(WeddingRegistry $registry): void
    {
        $registry->save();
    }
}
