<?php

namespace App\Repositories\Admin;

use App\Models\NavigationSetting;

class NavigationRepository
{
    public function current(bool $lock = false): ?NavigationSetting
    {
        return NavigationSetting::query()->when($lock, fn ($query) => $query->lockForUpdate())->find(1);
    }

    public function data(): array
    {
        $current = $this->current();
        $menu = array_map(function ($item) {
            $item['label'] = __('pages.'.$item['label']);

            return $item;
        }, config('website_navigation.items'));

        return ['menu' => $current?->menu ?? $menu, 'footer_links' => $current?->footer_links ?? [],
            'footer' => $current?->footer ?? ['brand' => __('pages.preview_sample_title'), 'description' => '', 'copyright' => '© '.date('Y').' Wedding', 'email' => '', 'phone' => '', 'address' => '', 'facebook' => '', 'instagram' => '', 'youtube' => '', 'tiktok' => ''],
            'display' => $current?->display ?? ['layout' => 'horizontal', 'logo_position' => 'left', 'show_icons' => false, 'new_tab' => false, 'show_footer' => true], 'logo' => $current?->logo];
    }

    public function save(array $data): NavigationSetting
    {
        $setting = $this->current() ?? new NavigationSetting;
        $setting->id = 1;
        $setting->fill($data)->save();

        return $setting;
    }
}
