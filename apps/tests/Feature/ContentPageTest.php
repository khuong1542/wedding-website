<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_save_an_allowlisted_blade_template_and_keep_legacy_sections(): void
    {
        $data = ['title' => 'Our home', 'slug' => '/', 'is_visible' => 1, 'blade_template' => 'classic', 'seo_title' => 'Our wedding'];
        $this->post(route('admin.pages.save'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $page = ContentPage::firstOrFail();
        $this->assertSame('classic', $page->blade_template);
        $legacy = [['type' => 'hero', 'title' => 'Previous layout', 'is_visible' => true]];
        $page->update(['sections' => $legacy]);
        $this->post(route('admin.pages.save'), array_replace($data, ['id' => $page->id, 'blade_template' => 'story']))->assertSessionHasNoErrors();
        $this->assertSame('story', $page->fresh()->blade_template);
        $this->assertSame($legacy, $page->fresh()->sections);
        $this->get(route('admin.pages.index', ['selected' => $page->id]))->assertOk()->assertSee('Our home')->assertSee('name="blade_template"', false)->assertDontSee('data-add-section', false);
        $this->patchJson(route('admin.pages.visibility', $page), ['is_visible' => false])->assertOk();
        $this->assertFalse($page->fresh()->is_visible);
    }

    public function test_unknown_blade_templates_and_duplicate_paths_are_rejected(): void
    {
        ContentPage::create(['title' => 'Home', 'slug' => '/', 'is_visible' => true, 'sections' => []]);
        $this->post(route('admin.pages.save'), ['title' => 'Duplicate', 'slug' => '/', 'is_visible' => 1, 'blade_template' => 'classic'])->assertSessionHasErrors('slug');
        $this->post(route('admin.pages.save'), ['title' => 'Invalid', 'slug' => '/valid', 'is_visible' => 1, 'blade_template' => 'admin.layouts.index'])->assertSessionHasErrors('blade_template');
        $this->assertDatabaseCount('content_pages', 1);
    }

    public function test_preview_renders_selected_blade_and_escapes_user_content_without_saving(): void
    {
        foreach (['classic', 'story', 'minimal'] as $template) {
            $response = $this->postJson(route('admin.pages.preview'), ['blade_template' => $template, 'title' => '<script>alert(1)</script>', 'summary' => 'Our wedding']);
            $response->assertOk()->assertJsonStructure(['html']);
            $html = $response->json('html');
            $this->assertStringContainsString('page-template-'.$template, $html);
            $this->assertStringContainsString('&lt;script&gt;', $html);
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        }
        $this->postJson(route('admin.pages.preview'), ['blade_template' => '../admin/layouts/index'])->assertStatus(422);
        $this->assertDatabaseCount('content_pages', 0);
    }

    public function test_page_photo_and_deletion(): void
    {
        Storage::fake('local');
        $image = UploadedFile::fake()->createWithContent('page.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->post(route('admin.pages.save'), ['title' => 'Gallery', 'slug' => '/gallery', 'is_visible' => 1, 'blade_template' => 'minimal', 'photo' => $image])->assertSessionHasNoErrors();
        $page = ContentPage::firstOrFail();
        Storage::disk('local')->assertExists($page->photo);
        $this->get(route('admin.pages.photo', $page))->assertOk()->assertHeader('content-type', 'image/png');
        $this->delete(route('admin.pages.delete', $page))->assertSessionHas('success');
        Storage::disk('local')->assertMissing($page->photo);
        $this->assertDatabaseCount('content_pages', 0);
    }

    public function test_repeated_previews_render_only_the_current_form_data(): void
    {
        foreach (['classic', 'minimal', 'story', 'classic'] as $index => $template) {
            $response = $this->postJson(route('admin.pages.preview'), ['blade_template' => $template, 'title' => 'Current title '.$index, 'summary' => 'Current summary '.$index]);
            $response->assertOk()->assertHeader('Cache-Control', 'no-store, private');
            $html = $response->json('html');
            $this->assertStringContainsString('page-template-'.$template, $html);
            $this->assertStringContainsString('Current title '.$index, $html);
            $this->assertStringContainsString('Current summary '.$index, $html);
            if ($index > 0) {
                $this->assertStringNotContainsString('Current title '.($index - 1), $html);
            }
            $this->assertSame(1, substr_count($html, '<!DOCTYPE html>'));
        }
        $this->postJson(route('admin.pages.preview'), ['blade_template' => 'minimal'])->assertOk()
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Our Wedding'));
        $this->assertDatabaseCount('content_pages', 0);
    }

    public function test_page_can_be_created_with_name_and_logo_without_editing_generated_metadata(): void
    {
        $data = ['title' => 'New name', 'blade_template' => 'classic'];
        $this->post(route('admin.pages.save'), $data)->assertSessionHasNoErrors();
        $page = ContentPage::firstOrFail();
        $this->assertStringStartsWith('/new-name-', $page->slug);
        $this->assertTrue($page->is_visible);
        $page->update(['summary' => 'Preserve summary', 'is_visible' => false]);
        $originalSlug = $page->slug;
        $this->post(route('admin.pages.save'), ['id' => $page->id, 'title' => 'Renamed', 'blade_template' => 'story'])->assertSessionHasNoErrors();
        $this->assertSame($originalSlug, $page->fresh()->slug);
        $this->assertSame('Preserve summary', $page->fresh()->summary);
        $this->assertFalse($page->fresh()->is_visible);
        $this->get(route('admin.pages.index', ['selected' => $page->id]))->assertOk()->assertDontSee('name="slug"', false)->assertDontSee('name="summary"', false)->assertSee('Logo');
        $this->postJson(route('admin.pages.preview'), $data)->assertOk()->assertJsonPath('html', fn ($html) => str_contains($html, 'template-skeleton'));
    }

    public function test_default_template_uses_version_two_skeleton_and_saved_couple_names(): void
    {
        $data = ['title' => 'Our invitation', 'blade_template' => 'classic', 'groom_name' => 'Test Groom', 'bride_name' => 'Test Bride'];
        $this->post(route('admin.pages.save'), $data)->assertSessionHasNoErrors();
        $page = ContentPage::firstOrFail();
        $this->assertSame('Test Groom', $page->groom_name);
        $this->assertSame('Test Bride', $page->bride_name);
        $this->get(route('admin.pages.index', ['selected' => $page->id]))->assertOk()->assertSee('Test Groom')->assertSee('Test Bride');
        $this->assertSame('templates.pages.version-2', config('page_templates.classic.view'));
        $response = $this->postJson(route('admin.pages.preview'), $data)->assertOk();
        $html = $response->json('html');
        foreach (['page-template-version-2', 'v2-hero', 'v2-couple', 'v2-story', 'v2-events', 'v2-gallery', 'v2-rsvp', 'v2-wishes', 'v2-gifts', 'Test Groom', 'Test Bride'] as $part) {
            $this->assertStringContainsString($part, $html);
        }
        $this->assertStringNotContainsString('images.unsplash.com', $html);
        $this->assertStringNotContainsString('assets/main.js', $html);
        $this->postJson(route('admin.pages.preview'), array_replace($data, ['bride_name' => str_repeat('a', 121)]))->assertStatus(422)->assertJsonValidationErrors('bride_name');
    }

    public function test_alternative_layouts_keep_the_selected_template_and_preview_as_skeletons(): void
    {
        Storage::fake('local');
        foreach (['story' => 'v1-locations', 'minimal' => 'v3-orbit', 'version-4' => 'v4-thanks'] as $template => $layout) {
            $data = ['title' => 'Invitation '.$template, 'blade_template' => $template, 'groom_name' => 'Minh Quân', 'bride_name' => 'Ngọc Linh', 'photo' => UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='))];
            $this->post(route('admin.pages.save'), $data)->assertSessionHasNoErrors();
            $page = ContentPage::where('title', $data['title'])->firstOrFail();
            $this->assertSame($template, $page->blade_template);
            unset($data['photo']);
            $data['id'] = $page->id;
            $html = $this->postJson(route('admin.pages.preview'), $data)->assertOk()->json('html');
            foreach ([$layout, 'template-skeleton', 'Minh Quân', 'Ngọc Linh', 'data:image/png;base64,'] as $part) {
                $this->assertStringContainsString($part, $html);
            }
            $this->assertStringNotContainsString('images.unsplash.com', $html);
            $this->assertStringNotContainsString('<script', $html);
        }
    }

    public function test_only_one_page_is_edited_and_repeated_saves_do_not_create_pages(): void
    {
        $this->get(route('admin.pages.index'))->assertOk()
            ->assertDontSee('data-page-search', false)->assertDontSee('data-page-card', false)
            ->assertDontSee('data-delete-dialog', false);
        $this->post(route('admin.pages.save'), ['title' => 'Our wedding', 'blade_template' => 'classic'])->assertSessionHasNoErrors();
        $page = ContentPage::firstOrFail();
        $this->post(route('admin.pages.save'), ['title' => 'Updated wedding', 'blade_template' => 'version-4'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.pages.index', ['lang' => app()->getLocale()]));
        $this->assertDatabaseCount('content_pages', 1);
        $this->assertSame('version-4', $page->fresh()->blade_template);
        $this->assertSame('Updated wedding', $page->fresh()->title);
        $legacy = ContentPage::create(['title' => 'Legacy page', 'slug' => '/legacy', 'sections' => []]);
        $this->get(route('admin.pages.index', ['new' => 1, 'selected' => $legacy->id]))->assertOk()
            ->assertSee('Updated wedding')->assertDontSee('Legacy page');
        $this->post(route('admin.pages.save'), ['id' => $legacy->id, 'title' => 'Single page', 'blade_template' => 'story'])->assertSessionHasNoErrors();
        $this->assertSame('Single page', $page->fresh()->title);
        $this->assertSame('Legacy page', $legacy->fresh()->title);
        $this->assertDatabaseCount('content_pages', 2);
    }
}
