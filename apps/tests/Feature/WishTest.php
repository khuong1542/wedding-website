<?php

namespace Tests\Feature;

use App\Models\Wish;
use App\Models\WishSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_status_can_be_changed_and_creation_editing_and_deletion_are_unavailable(): void
    {
        $this->get(route('admin.wishes.index'))->assertOk()->assertSee('No matching wishes.')->assertDontSee('Add wish');
        $this->get(route('admin.wishes.index', ['new' => 1]))->assertOk()->assertDontSee('name="message"', false);
        $this->post('/admin/wishes', ['name' => 'Guest', 'message' => 'New'])->assertStatus(405);
        $wish = Wish::create(['name' => 'Nguyễn An', 'email' => 'an@example.com', 'message' => 'Best wishes', 'status' => 'pending']);
        $this->get(route('admin.wishes.index', ['selected' => $wish->id, 'edit' => 1]))->assertOk()->assertSee('an@example.com')
            ->assertDontSee('name="message"', false)->assertDontSee('name="name"', false)->assertDontSee('data-wish-delete', false);
        $this->patchJson(route('admin.wishes.status', $wish), ['status' => 'published', 'message' => 'Changed', 'name' => 'Changed', 'email' => 'changed@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'message']);
        $this->patch(route('admin.wishes.status', $wish), ['status' => 'published'])->assertSessionHas('success');
        $this->assertSame('published', $wish->fresh()->status);
        $this->assertSame('Best wishes', $wish->fresh()->message);
        $this->assertSame('Nguyễn An', $wish->fresh()->name);
        $this->patch(route('admin.wishes.status', $wish), ['status' => 'hidden'])->assertSessionHas('success');
        $this->assertSame('hidden', $wish->fresh()->status);
        $this->delete('/admin/wishes/'.$wish->id)->assertNotFound();
        $this->assertDatabaseCount('wishes', 1);
        $this->get(route('admin.wishes.index', ['selected' => 0]))->assertOk()->assertDontSee('data-wish-status', false);
    }

    public function test_filters_validation_and_escaped_messages(): void
    {
        $wish = Wish::create(['name' => 'An', 'message' => '<script>alert(1)</script> special phrase', 'status' => 'published']);
        Wish::create(['name' => 'Other guest', 'message' => 'Other message', 'status' => 'pending']);
        $this->get(route('admin.wishes.index', ['q' => 'special phrase', 'status' => 'published', 'selected' => $wish->id]))->assertOk()->assertSee('An')->assertDontSee('Other guest')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->patchJson(route('admin.wishes.status', $wish), ['status' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertSame('published', $wish->fresh()->status);
        $this->get(route('admin.wishes.index', ['selected' => 999]))->assertNotFound();
        $this->get(route('admin.wishes.index', ['sort' => 'invalid']))->assertSessionHasErrors('sort');
    }

    public function test_bulk_actions_affect_only_selected_wishes_and_reject_invalid_selection(): void
    {
        $first = Wish::create(['name' => 'First', 'message' => 'One']);
        $second = Wish::create(['name' => 'Second', 'message' => 'Two']);
        $third = Wish::create(['name' => 'Third', 'message' => 'Three']);
        $this->post(route('admin.wishes.bulk'), ['action' => 'publish', 'ids' => [$first->id, $second->id]])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame('published', $first->fresh()->status);
        $this->assertSame('pending', $third->fresh()->status);
        $this->postJson(route('admin.wishes.bulk'), ['action' => 'publish', 'ids' => [$first->id, 999]])->assertUnprocessable()->assertJsonValidationErrors('ids.1');
        $this->assertDatabaseCount('wishes', 3);
        $this->post(route('admin.wishes.bulk'), ['action' => 'hide', 'ids' => [$first->id]])->assertSessionHasNoErrors();
        $this->assertSame('hidden', $first->fresh()->status);
        $this->postJson(route('admin.wishes.bulk'), ['action' => 'delete', 'ids' => [$first->id, $second->id]])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->assertDatabaseCount('wishes', 3);
    }

    public function test_display_settings_are_persisted_and_wishes_are_paginated(): void
    {
        $this->get(route('admin.wishes.index', ['tab' => 'settings']))->assertOk()->assertSee('Allow guests to send wishes');
        $this->assertDatabaseCount('wish_settings', 0);
        $data = ['is_visible' => 0, 'allow_submissions' => 1, 'require_approval' => 1, 'display_limit' => 25, 'sort' => 'oldest'];
        $this->put(route('admin.wishes.settings'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertFalse(WishSetting::firstOrFail()->is_visible);
        $this->assertSame(25, WishSetting::firstOrFail()->display_limit);
        $this->put(route('admin.wishes.settings'), array_replace($data, ['display_limit' => 0]))->assertSessionHasErrors('display_limit');
        $this->assertSame(25, WishSetting::firstOrFail()->display_limit);
        for ($index = 0; $index < 12; $index++) {
            Wish::create(['name' => 'Guest '.$index, 'message' => 'Best wishes']);
        }
        $this->get(route('admin.wishes.index', ['page' => 2]))->assertOk()->assertSee('Showing 11–12 of 12 wishes');
    }
}
