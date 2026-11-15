<?php

namespace Tests\Feature;

use App\Models\WeddingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeddingEventTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['side' => 'groom', 'title' => 'Wedding ceremony', 'type' => 'ceremony', 'date' => '2027-05-20', 'start_time' => '10:00', 'end_time' => '12:00', 'venue' => 'Test venue', 'map_url' => 'https://maps.google.com/?q=venue', 'description' => 'Our celebration', 'is_visible' => '1'];
    }

    public function test_events_are_saved_updated_and_scoped_by_side(): void
    {
        $this->post(route('admin.events.save'), $this->data())->assertSessionHasNoErrors()->assertSessionHas('success');
        $event = WeddingEvent::firstOrFail();
        $this->get('/admin/events?side=groom')->assertOk()->assertSee('Wedding ceremony')->assertSee('Our celebration');
        $this->get('/admin/events?side=bride')->assertOk()->assertDontSee('Wedding ceremony');
        $this->get('/admin/events?side=bride&event='.$event->id)->assertNotFound();
        $this->post(route('admin.events.save'), array_replace($this->data(), ['id' => $event->id, 'title' => 'Updated ceremony', 'is_visible' => '0']))->assertSessionHasNoErrors();
        $this->assertSame('Updated ceremony', $event->fresh()->title);
        $this->assertFalse($event->fresh()->is_visible);
        $this->assertDatabaseCount('wedding_events', 1);
        $this->get('/admin/events?new=1')->assertOk()->assertSee('New event');
    }

    public function test_invalid_event_details_are_rejected(): void
    {
        $this->post(route('admin.events.save'), array_replace($this->data(), ['title' => '', 'type' => 'invalid', 'end_time' => '09:00', 'map_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors(['title', 'type', 'end_time', 'map_url']);
        $this->assertDatabaseCount('wedding_events', 0);
    }

    public function test_photo_visibility_and_deletion(): void
    {
        Storage::fake('local');
        $image = UploadedFile::fake()->createWithContent('event.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->post(route('admin.events.save'), $this->data() + ['photo' => $image])->assertSessionHasNoErrors();
        $event = WeddingEvent::firstOrFail();
        Storage::disk('local')->assertExists($event->photo);
        $this->get(route('admin.events.photo', $event))->assertOk()->assertHeader('content-type', 'image/png');
        $this->patchJson(route('admin.events.visibility', $event), ['is_visible' => false])->assertOk();
        $this->assertFalse($event->fresh()->is_visible);
        $this->assertSame('Wedding ceremony', $event->fresh()->title);
        $this->delete(route('admin.events.destroy', $event))->assertSessionHas('success');
        Storage::disk('local')->assertMissing($event->photo);
        $this->assertDatabaseMissing('wedding_events', ['id' => $event->id]);
    }

    public function test_shared_events_use_one_list_without_side_tabs(): void
    {
        $this->post(route('admin.events.save'), $this->data())->assertSessionHasNoErrors();
        $separate = WeddingEvent::firstOrFail();
        $data = array_replace($this->data(), ['title' => 'Shared ceremony']);
        $this->post(route('admin.shared-events.save'), $data)->assertSessionHasNoErrors()
            ->assertRedirectContains('/admin/shared-events');
        $shared = WeddingEvent::where('side', 'shared')->firstOrFail();
        $this->get('/admin/shared-events')->assertOk()->assertSee('Shared ceremony')
            ->assertDontSee('Wedding ceremony')->assertDontSee('class="event-side-tabs"', false);
        $this->get('/admin/events')->assertOk()->assertSee('Wedding ceremony')->assertDontSee('Shared ceremony');
        $this->get('/admin/events?event='.$shared->id)->assertNotFound();
        $this->get('/admin/shared-events?event='.$separate->id)->assertNotFound();
        $this->post(route('admin.shared-events.save'), array_replace($data, ['id' => $separate->id]))->assertSessionHasErrors('id');
        $this->post(route('admin.events.save'), array_replace($this->data(), ['id' => $shared->id]))->assertSessionHasErrors('id');
        $this->post(route('admin.shared-events.save'), array_replace($data, ['id' => $shared->id, 'title' => 'Updated shared event']))->assertSessionHasNoErrors();
        $this->assertSame('Updated shared event', $shared->fresh()->title);
        $this->assertSame('shared', $shared->fresh()->side);
        $this->patchJson(route('admin.events.visibility', $shared), ['is_visible' => false])->assertOk();
        $this->assertFalse($shared->fresh()->is_visible);
        $this->delete(route('admin.events.destroy', $shared))->assertRedirectContains('/admin/shared-events');
        $this->assertDatabaseHas('wedding_events', ['id' => $separate->id, 'side' => 'groom']);
    }
}
