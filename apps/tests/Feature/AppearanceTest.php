<?php

namespace Tests\Feature;

use App\Models\AppearanceSetting;
use App\Models\ContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_screen_and_preview_do_not_create_settings_or_pages(): void
    {
        $this->get(route('admin.themes.index'))->assertOk()->assertSee('Choose a theme')->assertSee('Noto Serif')->assertSee('Noto Sans')->assertSee('data-appearance-device="mobile"', false);
        $this->get(route('admin.themes.show'))->assertOk()->assertSee('page-template-version-2', false)->assertSee('--wedding-primary: #D81B60', false);
        $this->assertDatabaseCount('appearance_settings', 0);
        $this->assertDatabaseCount('content_pages', 0);
    }

    public function test_appearance_is_saved_once_and_applied_to_the_selected_layout(): void
    {
        $page = ContentPage::create(['title' => 'Our wedding', 'slug' => '/', 'sections' => [], 'blade_template' => 'minimal', 'groom_name' => 'Minh Quân', 'bride_name' => 'Ngọc Linh']);
        $data = array_replace(config('appearance.defaults'), ['theme' => 'garden', 'primary' => '#4D7C46', 'body_font' => 'serif', 'font_size' => 16, 'animations' => 0, 'radius' => 20]);
        $this->put(route('admin.themes.save'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame('#4D7C46', AppearanceSetting::firstOrFail()->settings['primary']);
        $this->get(route('admin.themes.show'))->assertOk()->assertSee('Minh Quân')->assertSee('Ngọc Linh')->assertSee('page-template-version-3', false)->assertSee('--wedding-primary: #4D7C46', false)->assertSee('animation: none !important', false);
        $preview = $this->postJson(route('admin.pages.preview'), ['blade_template' => 'minimal', 'id' => $page->id])->assertOk()->json('html');
        $this->assertStringContainsString('--wedding-primary: #4D7C46', $preview);
        $this->assertStringContainsString('font-size: 16px', $preview);
        $this->put(route('admin.themes.save'), array_replace($data, ['primary' => '#112233']))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('appearance_settings', 1);
        $this->assertSame('minimal', $page->fresh()->blade_template);
    }

    public function test_preview_does_not_save_and_invalid_css_fonts_and_sizes_are_rejected(): void
    {
        $data = array_replace(config('appearance.defaults'), ['primary' => '#123456']);
        $html = $this->postJson(route('admin.themes.preview'), $data)->assertOk()->assertHeader('Cache-Control', 'no-store, private')->json('html');
        $this->assertStringContainsString('--wedding-primary: #123456', $html);
        $this->assertDatabaseCount('appearance_settings', 0);
        $this->putJson(route('admin.themes.save'), array_replace($data, ['primary' => 'red;display:none', 'theme' => '../admin', 'heading_font' => '<script>', 'font_size' => 99, 'content_width' => 100, 'radius' => -1, 'animations' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors(['primary', 'theme', 'heading_font', 'font_size', 'content_width', 'radius', 'animations']);
        $this->assertDatabaseCount('appearance_settings', 0);
    }
}
