<?php

namespace Tests\Feature;

use App\Models\RsvpResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_screen_has_no_creation_action_and_no_creation_endpoint(): void
    {
        $this->get(route('admin.rsvp.index'))->assertOk()->assertSee('No matching responses.')->assertDontSee('Add response');
        $this->post('/admin/rsvp', [])->assertStatus(405);
        $this->assertDatabaseCount('rsvp_responses', 0);
    }

    public function test_list_filters_and_details_use_saved_responses(): void
    {
        $response = RsvpResponse::create(['name' => 'Nguyễn An', 'email' => 'an@example.com', 'phone' => '0901234567', 'group' => 'family', 'status' => 'attending', 'attendees' => 2, 'notes' => 'Vegetarian meal', 'responded_at' => '2026-11-12 14:30:00']);
        RsvpResponse::create(['name' => 'Other guest', 'group' => 'friends', 'status' => 'pending']);
        $this->get(route('admin.rsvp.index', ['q' => 'an@example.com', 'group' => 'family', 'status' => 'attending', 'date' => '2026-11-12', 'selected' => $response->id]))
            ->assertOk()->assertSee('Nguyễn An')->assertSee('Vegetarian meal')->assertDontSee('Other guest')->assertSee('50.0%');
        $this->get(route('admin.rsvp.index', ['q' => '0901234567']))->assertOk()->assertSee('Nguyễn An')->assertDontSee('Other guest');
        $this->get(route('admin.rsvp.index', ['selected' => $response->id, 'edit' => 1, 'lang' => 'vn']))->assertOk()->assertSee('Lưu thay đổi')->assertSee('name="attendees"', false);
        $this->get(route('admin.rsvp.index', ['status' => 'invalid']))->assertSessionHasErrors('status');
        $this->get(route('admin.rsvp.index', ['selected' => 999]))->assertNotFound();
    }

    public function test_update_validates_and_normalizes_attendance_without_creating_records(): void
    {
        $response = RsvpResponse::create(['name' => 'Guest', 'group' => 'family', 'status' => 'pending']);
        $data = ['name' => 'Updated guest', 'group' => 'friends', 'status' => 'attending', 'attendees' => 2, 'notes' => '<script>alert(1)</script>'];
        $this->put(route('admin.rsvp.update', $response), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(2, $response->fresh()->attendees);
        $this->assertNotNull($response->fresh()->responded_at);
        $this->get(route('admin.rsvp.index', ['selected' => $response->id]))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->putJson(route('admin.rsvp.update', $response), array_replace($data, ['name' => '', 'attendees' => 0]))->assertUnprocessable()->assertJsonValidationErrors(['name', 'attendees']);
        $this->assertSame('Updated guest', $response->fresh()->name);
        $this->put(route('admin.rsvp.update', $response), array_replace($data, ['status' => 'declined']))->assertSessionHasNoErrors();
        $this->assertSame(0, $response->fresh()->attendees);
        $this->put(route('admin.rsvp.update', $response), array_replace($data, ['status' => 'pending']))->assertSessionHasNoErrors();
        $this->assertNull($response->fresh()->responded_at);
        $this->assertDatabaseCount('rsvp_responses', 1);
    }

    public function test_pagination_and_deletion(): void
    {
        for ($index = 0; $index < 12; $index++) {
            RsvpResponse::create(['name' => 'Guest '.$index]);
        }
        $this->get(route('admin.rsvp.index', ['page' => 2]))->assertOk()->assertSee('Showing 11–12 of 12 responses');
        $response = RsvpResponse::firstOrFail();
        $this->delete(route('admin.rsvp.destroy', $response))->assertSessionHas('success');
        $this->assertDatabaseMissing('rsvp_responses', ['id' => $response->id]);
        $this->delete(route('admin.rsvp.destroy', $response))->assertNotFound();
    }
}
