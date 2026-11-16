<?php

namespace Tests\Feature;

use App\Models\AppearanceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_screen_and_default_preview_do_not_create_settings(): void
    {
        $this->get(route('admin.ui-settings.index'))->assertOk()->assertSee('Display mode')->assertSee('name="mode"', false)->assertSee('name="spacing"', false)->assertSee('Noto Sans')->assertSee('data-appearance-viewport', false);
        $this->get(route('admin.ui-settings.index', ['lang' => 'vn']))->assertOk()->assertSee('Chế độ hiển thị')->assertSee('Bo góc');
        $this->get(route('admin.ui-settings.show'))->assertOk()->assertSee('color-scheme: light', false);
        $this->assertDatabaseCount('appearance_settings', 0);
    }

    public function test_ui_options_save_and_share_appearance_without_losing_values(): void
    {
        $data = array_replace(config('appearance.defaults'), ['mode' => 'dark', 'layout' => 'boxed', 'radius' => 20, 'shadow' => 'strong', 'spacing' => 24, 'primary' => '#123456', 'button_style' => 'outline', 'button_size' => 'large', 'smooth_scroll' => false]);
        $this->put(route('admin.ui-settings.save'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $settings = AppearanceSetting::firstOrFail()->settings;
        $this->assertSame('dark', $settings['mode']);
        $this->assertSame(24, $settings['spacing']);
        $this->assertSame('#123456', $settings['primary']);
        $this->get(route('admin.ui-settings.show'))->assertOk()->assertSee('color-scheme: dark', false)->assertSee('--wedding-spacing: 24px', false)->assertSee('scroll-behavior: auto', false);
        $themeData = array_replace(config('appearance.defaults'), ['primary' => '#AABBCC']);
        $this->put(route('admin.themes.save'), $themeData)->assertSessionHasNoErrors();
        $settings = AppearanceSetting::firstOrFail()->settings;
        $this->assertSame('dark', $settings['mode']);
        $this->assertSame('outline', $settings['button_style']);
        $this->assertSame('#AABBCC', $settings['primary']);
        $this->assertDatabaseCount('appearance_settings', 1);
    }

    public function test_unsaved_preview_and_invalid_values_do_not_change_database(): void
    {
        $data = array_replace(config('appearance.defaults'), ['mode' => 'system', 'layout' => 'wide', 'shadow' => 'medium', 'spacing' => 30]);
        $html = $this->postJson(route('admin.ui-settings.preview'), $data)->assertOk()->assertHeader('Cache-Control', 'no-store, private')->json('html');
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);
        $this->assertStringContainsString('--wedding-spacing: 30px', $html);
        $this->assertDatabaseCount('appearance_settings', 0);
        $this->putJson(route('admin.ui-settings.save'), array_replace($data, ['mode' => 'invalid', 'layout' => 'invalid', 'shadow' => 'url(evil)', 'spacing' => -1, 'button_style' => '<script>', 'button_size' => 'invalid', 'primary' => 'red', 'smooth_scroll' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors(['mode', 'layout', 'shadow', 'spacing', 'button_style', 'button_size', 'primary', 'smooth_scroll']);
        $this->assertDatabaseCount('appearance_settings', 0);
    }
}
