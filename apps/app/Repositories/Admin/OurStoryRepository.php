<?php

namespace App\Repositories\Admin;

use App\Models\OurStory;
use App\Models\StoryMilestone;
use Illuminate\Database\Eloquent\Collection;

class OurStoryRepository
{
    public function find(): ?OurStory
    {
        return OurStory::with('milestones')->find(1);
    }

    public function lock(bool $create = false): OurStory
    {
        if ($create) {
            OurStory::query()->insertOrIgnore(['id' => 1, 'title' => '', 'created_at' => now(), 'updated_at' => now()]);
        }

        return OurStory::query()->lockForUpdate()->findOrFail(1);
    }

    public function update(OurStory $story, array $data): void
    {
        $story->update($data);
    }

    public function milestones(OurStory $story): Collection
    {
        return $story->milestones()->get()->keyBy('id');
    }

    public function makeMilestone(OurStory $story): StoryMilestone
    {
        return $story->milestones()->make();
    }

    public function saveMilestone(StoryMilestone $milestone): void
    {
        $milestone->save();
    }

    public function deleteMilestone(StoryMilestone $milestone): void
    {
        $milestone->delete();
    }

    public function ids(OurStory $story): array
    {
        return $story->milestones()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    public function setPosition(OurStory $story, int $id, int $position): void
    {
        $story->milestones()->whereKey($id)->update(['position' => $position]);
    }

    public function photoMilestone(int $id): StoryMilestone
    {
        return StoryMilestone::where('our_story_id', 1)->findOrFail($id);
    }
}
