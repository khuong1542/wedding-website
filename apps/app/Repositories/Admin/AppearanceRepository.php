<?php

namespace App\Repositories\Admin;

use App\Models\AppearanceSetting;

class AppearanceRepository
{
    public function settings(): array
    {
        return array_replace(config('appearance.defaults'), AppearanceSetting::find(1)?->settings ?? []);
    }

    public function save(array $settings): void
    {
        $appearance = AppearanceSetting::find(1) ?? new AppearanceSetting;
        $appearance->id = 1;
        $appearance->settings = $settings;
        $appearance->save();
    }
}
