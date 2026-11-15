<?php

namespace Tests\Feature;

use App\Models\CoupleProfile;
use App\Models\WeddingRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeddingRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_settings_and_gifts_are_saved_and_loaded(): void
    {
        $this->put(route('admin.registry.update'), [
            'enabled' => '1', 'bride_enabled' => '1', 'groom_enabled' => '0',
            'bride_bank' => 'Test Bank', 'bride_account' => '00123456', 'bride_holder' => 'Test Bride',
            'message' => 'Thank you', 'instructions' => 'A note for guests',
            'gifts' => [['title' => 'Coffee maker', 'url' => 'https://example.com/gift', 'note' => 'For our home']],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $registry = WeddingRegistry::findOrFail(1);
        $this->assertSame('00123456', $registry->settings['bride_account']);
        $this->assertSame('Coffee maker', $registry->gifts[0]['title']);
        $this->get('/admin?page=registry')->assertOk()->assertSee('Test Bank')->assertSee('Thank you');
        $this->put(route('admin.registry.update'), ['enabled' => '0', 'bride_enabled' => '0', 'groom_enabled' => '0'])->assertSessionHasNoErrors();
        $this->assertSame('Coffee maker', $registry->fresh()->gifts[0]['title']);
        $this->assertDatabaseCount('wedding_registries', 1);
    }

    public function test_qr_is_shared_with_couple_profile_without_overwriting_details(): void
    {
        Storage::fake('local');
        CoupleProfile::forceCreate(['id' => 1, 'details' => ['bride_full_name' => 'Existing Bride']]);
        $data = ['enabled' => '0', 'bride_enabled' => '0', 'groom_enabled' => '0'];
        $image = UploadedFile::fake()->createWithContent('qr.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->put(route('admin.registry.update'), $data + ['bride_bank_qr' => $image])->assertSessionHasNoErrors();
        $couple = CoupleProfile::findOrFail(1);
        $this->assertSame('Existing Bride', $couple->details['bride_full_name']);
        Storage::disk('local')->assertExists($couple->bride_bank_qr);
        $this->get(route('admin.couple.qr', 'bride'))->assertOk();
        $this->put(route('admin.registry.update'), $data + ['remove_bride_bank_qr' => '1'])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($couple->bride_bank_qr);
        $this->assertNull($couple->fresh()->bride_bank_qr);
    }

    public function test_visible_accounts_require_bank_details_and_invalid_gifts_are_rejected(): void
    {
        $this->put(route('admin.registry.update'), [
            'enabled' => '1', 'bride_enabled' => '1', 'groom_enabled' => '0',
            'gifts' => [['title' => '', 'url' => 'javascript:alert(1)']],
        ])->assertSessionHasErrors(['bride_bank', 'bride_account', 'bride_holder', 'gifts.0.title', 'gifts.0.url']);
        $this->assertDatabaseCount('wedding_registries', 0);
    }
}
