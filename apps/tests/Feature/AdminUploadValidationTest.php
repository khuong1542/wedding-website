<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('selected.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
    }

    public function test_upload_forms_return_json_validation_without_redirecting(): void
    {
        Storage::fake('local');
        $this->withHeader('Accept', 'application/json');
        $this->put(route('admin.couple.update'), ['groom_photo' => $this->image()])
            ->assertStatus(422)->assertJsonValidationErrors('groom_full_name');
        $this->put(route('admin.our-story.update'), ['title' => 'Our story', 'milestones' => [['title' => '', 'photo' => $this->image()]]])
            ->assertStatus(422)->assertJsonValidationErrors('milestones.0.title');
        $this->put(route('admin.registry.update'), ['bride_enabled' => '1', 'bride_bank_qr' => $this->image()])
            ->assertStatus(422)->assertJsonValidationErrors('bride_bank');
        $this->post(route('admin.events.save'), ['photo' => $this->image()])
            ->assertStatus(422)->assertJsonValidationErrors('title');
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_ajax_save_returns_destination_without_consuming_success_flash(): void
    {
        Storage::fake('local');
        $response = $this->withHeader('Accept', 'application/json')->put(route('admin.couple.update'), [
            'groom_full_name' => 'Groom', 'bride_full_name' => 'Bride', 'groom_photo' => $this->image(),
        ])->assertOk()->assertJsonStructure(['redirect'])->assertSessionHas('couple_saved');
        $this->get($response->json('redirect'))->assertOk()->assertSee('data-toast-type="success"', false);
    }
}
