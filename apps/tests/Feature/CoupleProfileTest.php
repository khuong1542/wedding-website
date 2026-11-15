<?php

namespace Tests\Feature;

use App\Models\CoupleProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoupleProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_is_saved_and_loaded_from_the_database(): void
    {
        $data = ['groom_full_name' => 'Test Groom', 'bride_full_name' => 'Test Bride', 'wedding_on' => '2027-05-20', 'quote' => 'Our story'];
        $this->put(route('admin.couple.update'), $data)
            ->assertRedirect('/admin?page=wedding&lang=en')->assertSessionHas('couple_saved');
        $profile = CoupleProfile::findOrFail(1);
        $this->assertSame('Test Groom', $profile->details['groom_full_name']);
        $this->assertSame('2027-05-20', $profile->details['wedding_on']);
        $this->get('/admin?page=wedding')->assertOk()->assertSee('Test Groom')->assertSee('data-toast-type="success"', false);
        $this->assertSame('Our story', $profile->details['quote']);
        $data['groom_full_name'] = 'Updated Groom';
        $this->put(route('admin.couple.update'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('couple_profiles', 1);
        $this->assertSame('Updated Groom', $profile->fresh()->details['groom_full_name']);
    }

    public function test_invalid_data_does_not_change_the_profile(): void
    {
        $this->from('/admin?page=wedding')->put(route('admin.couple.update'), [
            'groom_full_name' => '', 'bride_full_name' => 'Bride',
            'groom_email' => 'invalid', 'bride_facebook' => 'javascript:alert(1)',
            'groom_birthday' => '2999-01-01',
        ])->assertRedirect('/admin?page=wedding')
            ->assertSessionHasErrors(['groom_full_name', 'groom_email', 'bride_facebook', 'groom_birthday']);
        $this->assertDatabaseCount('couple_profiles', 0);
    }

    public function test_photos_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('local');
        $data = ['groom_full_name' => 'Groom', 'bride_full_name' => 'Bride'];
        $this->put(route('admin.couple.update'), $data + ['groom_photo' => $this->photo('portrait.png')])->assertSessionHasNoErrors();
        $oldPath = CoupleProfile::findOrFail(1)->groom_photo;
        Storage::disk('local')->assertExists($oldPath);
        $this->get(route('admin.couple.photo', 'groom'))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('admin.couple.photo', 'bride'))->assertNotFound();
        $this->get(route('admin.couple.photo', 'invalid'))->assertNotFound();
        $this->put(route('admin.couple.update'), $data + ['groom_photo' => $this->photo('new.png')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($oldPath);
        $newPath = CoupleProfile::findOrFail(1)->groom_photo;
        Storage::disk('local')->assertExists($newPath);
        $this->put(route('admin.couple.update'), $data)->assertSessionHasNoErrors();
        $this->assertSame($newPath, CoupleProfile::findOrFail(1)->groom_photo);
        $this->put(route('admin.couple.update'), $data + ['remove_groom_photo' => '1'])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($newPath);
        $this->assertNull(CoupleProfile::findOrFail(1)->groom_photo);
    }

    public function test_unsupported_or_oversized_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $this->put(route('admin.couple.update'), [
            'groom_full_name' => 'Groom', 'bride_full_name' => 'Bride',
            'groom_photo' => UploadedFile::fake()->create('photo.svg', 10, 'image/svg+xml'),
            'bride_photo' => $this->photo('large.png')->size(2049),
        ])->assertSessionHasErrors(['groom_photo', 'bride_photo']);
        $this->assertDatabaseCount('couple_profiles', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_addresses_and_bank_qr_codes_are_saved_and_can_be_removed_independently(): void
    {
        Storage::fake('local');
        $data = ['groom_full_name' => 'Groom', 'bride_full_name' => 'Bride', 'groom_address' => 'Groom address', 'bride_address' => 'Bride address'];
        $this->put(route('admin.couple.update'), $data + [
            'groom_bank_qr' => $this->photo('groom-qr.png'),
            'bride_bank_qr' => $this->photo('bride-qr.png'),
        ])->assertSessionHasNoErrors();
        $profile = CoupleProfile::findOrFail(1);
        $this->assertSame('Groom address', $profile->details['groom_address']);
        $this->assertSame('Bride address', $profile->details['bride_address']);
        Storage::disk('local')->assertExists($profile->groom_bank_qr);
        Storage::disk('local')->assertExists($profile->bride_bank_qr);
        $this->get(route('admin.couple.qr', 'groom'))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('admin.couple.qr', 'bride'))->assertOk();
        $this->put(route('admin.couple.update'), $data + ['remove_groom_bank_qr' => '1'])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($profile->groom_bank_qr);
        Storage::disk('local')->assertExists($profile->bride_bank_qr);
        $this->assertNull($profile->fresh()->groom_bank_qr);
        $this->get(route('admin.couple.qr', 'groom'))->assertNotFound();
        $this->get('/admin?page=wedding')->assertOk()
            ->assertSee('groom_address')->assertSee('bride_bank_qr')
            ->assertDontSee('name="groom_display_name"', false)
            ->assertDontSee('name="bride_occupation"', false)
            ->assertDontSee('name="groom_hometown"', false);
    }

    private function photo(string $name): File
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='
        ));
    }
}
