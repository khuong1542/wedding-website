<?php

namespace Tests\Feature;

use App\Models\OurStory;
use App\Models\StoryMilestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OurStoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_and_milestones_can_be_saved_reordered_and_deleted(): void
    {
        $this->put(route('admin.our-story.update'), [
            'title' => 'Our journey', 'introduction' => 'How we met',
            'milestones' => [['title' => 'First meeting', 'date' => '2020-01-01'], ['title' => 'Engagement']],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $story = OurStory::with('milestones')->findOrFail(1);
        $this->assertSame('Our journey', $story->title);
        $first = $story->milestones[0];
        $second = $story->milestones[1];
        $this->get('/admin/our-story')->assertOk()->assertSee('First meeting');
        $this->assertSame('How we met', $story->introduction);
        $this->put(route('admin.our-story.update'), [
            'title' => 'Our journey', 'milestones' => [
                8 => ['id' => $second->id, 'title' => 'Engaged'],
                2 => ['id' => $first->id, 'title' => 'First meeting', 'date' => '2020-01-01'],
            ],
        ])->assertSessionHasNoErrors();
        $this->assertSame([$second->id, $first->id], $story->milestones()->pluck('id')->all());
        $this->get('/admin/our-story')->assertOk()->assertSeeInOrder(['Engaged', 'First meeting']);
        $this->put(route('admin.our-story.update'), ['title' => 'Just us'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('story_milestones', 0);
        $this->assertDatabaseCount('our_stories', 1);
    }

    public function test_milestone_image_is_served_replaced_and_deleted(): void
    {
        Storage::fake('local');
        $image = fn () => UploadedFile::fake()->createWithContent('moment.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
        $this->put(route('admin.our-story.update'), ['title' => 'Our story', 'milestones' => [['title' => 'A moment', 'photo' => $image()]]])->assertSessionHasNoErrors();
        $milestone = StoryMilestone::firstOrFail();
        $path = $milestone->photo;
        Storage::disk('local')->assertExists($path);
        $this->get(route('admin.our-story.photo', $milestone))->assertOk()->assertHeader('content-type', 'image/png');
        $this->put(route('admin.our-story.update'), ['title' => 'Our story', 'milestones' => [['id' => $milestone->id, 'title' => 'A moment', 'photo' => $image()]]])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        $path = $milestone->fresh()->photo;
        $this->put(route('admin.our-story.update'), ['title' => 'Our story'])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        $this->get(route('admin.our-story.photo', $milestone))->assertNotFound();
    }

    public function test_invalid_milestones_do_not_write_data(): void
    {
        $this->put(route('admin.our-story.update'), [
            'title' => '', 'milestones' => [['id' => 999, 'title' => '', 'date' => 'invalid', 'photo' => UploadedFile::fake()->create('unsafe.svg', 10, 'image/svg+xml')]],
        ])->assertSessionHasErrors(['title', 'milestones.0.id', 'milestones.0.title', 'milestones.0.date', 'milestones.0.photo']);
        $this->assertDatabaseCount('our_stories', 0);
        $this->assertDatabaseCount('story_milestones', 0);
    }

    public function test_milestone_visibility_is_persisted(): void
    {
        $this->put(route('admin.our-story.update'), [
            'title' => 'Our story', 'milestones' => [['title' => 'Private moment', 'is_visible' => '0']],
        ])->assertSessionHasNoErrors();
        $milestone = StoryMilestone::firstOrFail();
        $this->assertFalse($milestone->is_visible);
        $this->put(route('admin.our-story.update'), [
            'title' => 'Our story', 'milestones' => [['id' => $milestone->id, 'title' => 'Public moment', 'is_visible' => '1']],
        ])->assertSessionHasNoErrors();
        $this->assertTrue($milestone->fresh()->is_visible);
    }

    public function test_order_endpoint_updates_only_positions_and_rejects_incomplete_or_duplicate_ids(): void
    {
        $story = OurStory::forceCreate(['id' => 1, 'title' => 'Our story']);
        $first = $story->milestones()->create(['title' => 'First', 'description' => 'Keep this draft', 'position' => 0, 'is_visible' => false]);
        $second = $story->milestones()->create(['title' => 'Second', 'position' => 1]);
        $this->patchJson(route('admin.our-story.order'), ['ids' => [$second->id, $first->id]])->assertOk()->assertJson(['saved' => true]);
        $this->assertSame([$second->id, $first->id], $story->milestones()->pluck('id')->all());
        $this->assertSame('Keep this draft', $first->fresh()->description);
        $this->assertFalse($first->fresh()->is_visible);
        $this->patchJson(route('admin.our-story.order'), ['ids' => [$first->id]])->assertStatus(422);
        $this->patchJson(route('admin.our-story.order'), ['ids' => [$first->id, $first->id]])->assertStatus(422);
        $this->patchJson(route('admin.our-story.order'), ['ids' => [$first->id, 99999]])->assertStatus(422);
        $this->assertSame([$second->id, $first->id], $story->milestones()->pluck('id')->all());
        $this->get('/admin/our-story')->assertOk()->assertSeeInOrder(['Second', 'First']);
    }

    public function test_milestone_titles_are_limited_to_120_characters(): void
    {
        $this->put(route('admin.our-story.update'), ['title' => 'Our story', 'milestones' => [['title' => str_repeat('a', 121)]]])
            ->assertSessionHasErrors('milestones.0.title');
        $this->assertDatabaseCount('story_milestones', 0);
    }
}
