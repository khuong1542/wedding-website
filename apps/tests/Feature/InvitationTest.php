<?php

namespace Tests\Feature;

use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        return array_replace(['title' => 'Main invitation', 'status' => 'active', 'template' => 'floral', 'accent' => '#D81B60', 'groom_name' => 'Minh Quân', 'bride_name' => 'Ngọc Linh', 'date' => '2026-12-20', 'time' => '10:00', 'venue' => 'Riverside Palace', 'message' => 'Please join us'], $overrides);
    }

    public function test_empty_screen_template_library_and_create_save_update(): void
    {
        $this->get(route('admin.invitations.index'))->assertOk()->assertSee('Create invitation')->assertSee('No matching invitations.');
        $this->get(route('admin.invitations.index', ['tab' => 'templates']))->assertOk()->assertSee('Use template')->assertSee('Rose floral');
        $this->get(route('admin.invitations.index', ['new' => 1, 'template' => 'elegant']))->assertOk();
        $this->assertDatabaseCount('invitations', 0);
        $this->post(route('admin.invitations.save'), $this->data())->assertSessionHasNoErrors()->assertSessionHas('success');
        $invitation = Invitation::firstOrFail();
        $this->get(route('admin.invitations.index', ['selected' => $invitation->id, 'lang' => 'vn']))->assertOk()->assertSee('Minh Quân')->assertSee('Đang sử dụng');
        $this->post(route('admin.invitations.save'), $this->data(['id' => $invitation->id, 'status' => 'archived', 'template' => 'minimal']))->assertSessionHasNoErrors();
        $this->assertSame('archived', $invitation->fresh()->status);
        $this->assertDatabaseCount('invitations', 1);
    }

    public function test_validation_preview_and_filters(): void
    {
        $this->postJson(route('admin.invitations.save'), $this->data(['title' => '', 'template' => '../admin', 'accent' => 'red', 'date' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors(['title', 'template', 'accent', 'date']);
        foreach (array_keys(config('invitation_templates')) as $template) {
            $html = $this->postJson(route('admin.invitations.preview'), $this->data(['template' => $template, 'message' => '<script>bad()</script>']))->assertOk()->json('html');
            $this->assertStringContainsString('iv-art-'.$template, $html);
            $this->assertStringContainsString('Minh Quân', $html);
            $this->assertStringContainsString('&lt;script&gt;', $html);
            $this->assertStringNotContainsString('<script>bad()', $html);
        }
        $this->assertDatabaseCount('invitations', 0);
        Invitation::create($this->data());
        Invitation::create($this->data(['title' => 'Hidden draft', 'status' => 'draft']));
        $this->get(route('admin.invitations.index', ['q' => 'Main', 'status' => 'active']))->assertOk()->assertSee('Main invitation')->assertDontSee('Hidden draft');
        $this->get(route('admin.invitations.index', ['status' => 'invalid']))->assertSessionHasErrors('status');
        $this->get(route('admin.invitations.index', ['selected' => 999]))->assertNotFound();
    }

    public function test_upload_duplicate_preview_and_delete_have_independent_images(): void
    {
        Storage::fake('local');
        $image = UploadedFile::fake()->createWithContent('thumb.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->post(route('admin.invitations.save'), $this->data(['photo' => $image]))->assertSessionHasNoErrors();
        $source = Invitation::firstOrFail();
        Storage::disk('local')->assertExists($source->photo);
        $this->get(route('admin.invitations.photo', $source))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('admin.invitations.show', $source))->assertOk()->assertSee('data:image/png;base64,', false);
        $this->post(route('admin.invitations.duplicate', $source))->assertSessionHas('success');
        $copy = Invitation::where('id', '!=', $source->id)->firstOrFail();
        $this->assertSame('draft', $copy->status);
        $this->assertNotSame($source->photo, $copy->photo);
        Storage::disk('local')->assertExists($copy->photo);
        $this->delete(route('admin.invitations.destroy', $source))->assertSessionHas('success');
        Storage::disk('local')->assertMissing($source->photo);
        Storage::disk('local')->assertExists($copy->photo);
        $this->post(route('admin.invitations.save'), $this->data(['id' => $copy->id, 'remove_photo' => 1]))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($copy->photo);
        $this->assertNull($copy->fresh()->photo);
    }
}
