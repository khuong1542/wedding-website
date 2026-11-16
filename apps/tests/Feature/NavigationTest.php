<?php

namespace Tests\Feature;

use App\Models\NavigationSetting;
use App\Repositories\Admin\NavigationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return array_diff_key(app(NavigationRepository::class)->data(), ['logo' => true]);
    }

    public function test_screen_preview_and_default_data_do_not_create_records(): void
    {
        $this->get(route('admin.navigation.index'))->assertOk()->assertSee('Manage navigation menu')->assertSee('enctype="multipart/form-data"', false)->assertSee('data-nf-list="footer_links"', false);
        $this->get(route('admin.navigation.index', ['lang' => 'vn']))->assertOk()->assertSee('Quản lý menu điều hướng');
        $this->postJson(route('admin.navigation.preview'), $this->data())->assertOk()->assertJsonStructure(['html']);
        $this->assertDatabaseCount('navigation_settings', 0);
    }

    public function test_order_visibility_footer_and_settings_are_saved_and_applied_to_templates(): void
    {
        $data = $this->data();
        $data['menu'] = array_reverse($data['menu']);
        $data['menu'][0]['label'] = 'Gift registry';
        $data['menu'][1]['label'] = 'Hidden item';
        $data['menu'][1]['visible'] = false;
        $data['footer_links'] = [$data['menu'][0]];
        $data['footer']['brand'] = 'Our celebration';
        $data['footer']['description'] = 'Thank you for joining us';
        $data['footer']['email'] = 'hello@example.com';
        $data['footer']['facebook'] = 'https://www.facebook.com/example';
        $data['display']['show_icons'] = true;
        $data['display']['new_tab'] = true;
        $data['display']['logo_position'] = 'center';
        $this->put(route('admin.navigation.save'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $setting = NavigationSetting::firstOrFail();
        $this->assertSame($data['menu'][0]['id'], $setting->menu[0]['id']);
        foreach (array_keys(config('page_templates')) as $template) {
            $html = $this->postJson(route('admin.pages.preview'), ['blade_template' => $template])->assertOk()->json('html');
            $this->assertStringContainsString('Gift registry', $html);
            $this->assertStringNotContainsString('Hidden item', $html);
            $this->assertStringContainsString('Our celebration', $html);
            $this->assertStringContainsString('mailto:hello@example.com', $html);
            $this->assertStringContainsString('website-logo-center', $html);
            $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        }
        $data['display']['show_footer'] = false;
        $this->put(route('admin.navigation.save'), $data)->assertSessionHasNoErrors();
        $this->get(route('admin.navigation.show'))->assertOk()->assertDontSee('website-footer-grid', false);
        $this->assertDatabaseCount('navigation_settings', 1);
    }

    public function test_invalid_urls_duplicate_ids_and_html_are_handled_without_saving_preview(): void
    {
        $data = $this->data();
        foreach (['javascript:alert(1)', '//evil.example', 'data:text/html,bad', '/\\evil.example'] as $url) {
            $data['menu'][0]['url'] = $url;
            $this->putJson(route('admin.navigation.save'), $data)->assertUnprocessable()->assertJsonValidationErrors('menu.0.url');
        }
        $data = $this->data();
        $data['menu'][1]['id'] = $data['menu'][0]['id'];
        $this->putJson(route('admin.navigation.save'), $data)->assertUnprocessable()->assertJsonValidationErrors('menu.0.id');
        $data = $this->data();
        $data['menu'][0]['label'] = '<script>bad()</script>';
        $html = $this->postJson(route('admin.navigation.preview'), $data)->assertOk()->json('html');
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>bad()', $html);
        $this->assertDatabaseCount('navigation_settings', 0);
    }

    public function test_multipart_json_lists_and_footer_logo_can_be_saved_and_removed(): void
    {
        Storage::fake('local');
        $data = $this->data();
        $data['menu'] = json_encode($data['menu']);
        $data['footer_links'] = '[]';
        $data['logo'] = UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->put(route('admin.navigation.save'), $data)->assertSessionHasNoErrors();
        $path = NavigationSetting::firstOrFail()->logo;
        Storage::disk('local')->assertExists($path);
        $this->get(route('admin.navigation.logo'))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('admin.navigation.show'))->assertOk()->assertSee('data:image/png;base64,', false);
        unset($data['logo']);
        $this->put(route('admin.navigation.save'), $data + ['remove_logo' => 1])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        $this->get(route('admin.navigation.logo'))->assertNotFound();
        $data['menu'] = '[]';
        $this->put(route('admin.navigation.save'), $data)->assertSessionHasNoErrors();
        $this->assertSame([], NavigationSetting::firstOrFail()->menu);
    }
}
