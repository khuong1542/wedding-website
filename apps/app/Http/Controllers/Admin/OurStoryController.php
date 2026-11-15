<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOurStoryRequest;
use App\Models\OurStory;
use App\Models\StoryMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OurStoryController extends Controller
{
    public function update(UpdateOurStoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $newPaths = [];
        $oldPaths = [];
        try {
            foreach (array_keys($data['milestones'] ?? []) as $key) {
                if ($request->hasFile("milestones.$key.photo")) {
                    $path = $request->file("milestones.$key.photo")->store('our-story', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store story photo.');
                    }
                    $newPaths[$key] = $path;
                }
            }
            DB::transaction(function () use ($data, $newPaths, &$oldPaths): void {
                DB::table('our_stories')->insertOrIgnore(['id' => 1, 'title' => '', 'created_at' => now(), 'updated_at' => now()]);
                $story = OurStory::query()->lockForUpdate()->findOrFail(1);
                $story->update(Arr::only($data, ['title', 'introduction']));
                $existing = $story->milestones()->get()->keyBy('id');
                $kept = [];
                foreach (array_keys($data['milestones'] ?? []) as $position => $key) {
                    $item = $data['milestones'][$key];
                    $milestone = ! empty($item['id']) ? $existing->get($item['id']) : $story->milestones()->make();
                    abort_unless($milestone, 422);
                    $milestone->fill(Arr::only($item, ['title', 'date', 'description', 'is_visible']));
                    $milestone->position = $position;
                    if (isset($newPaths[$key]) || ! empty($item['remove_photo'])) {
                        if ($milestone->photo) {
                            $oldPaths[] = $milestone->photo;
                        }
                        $milestone->photo = $newPaths[$key] ?? null;
                    }
                    $milestone->save();
                    $kept[] = $milestone->id;
                }
                foreach ($existing as $milestone) {
                    if (! in_array($milestone->id, $kept, true)) {
                        if ($milestone->photo) {
                            $oldPaths[] = $milestone->photo;
                        }
                        $milestone->delete();
                    }
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($newPaths));
            throw $exception;
        }
        Storage::disk('local')->delete($oldPaths);

        return redirect()->route('admin.dashboard', ['page' => 'our-story', 'lang' => app()->getLocale()])->with('success', __('story.saved'));
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['present', 'array', 'max:30'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);
        DB::transaction(function () use ($data): void {
            $story = OurStory::query()->lockForUpdate()->findOrFail(1);
            $current = $story->milestones()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $requested = array_map('intval', $data['ids']);
            $sorted = $requested;
            sort($sorted);
            abort_unless($current === $sorted, 422);
            foreach ($requested as $position => $id) {
                $story->milestones()->whereKey($id)->update(['position' => $position]);
            }
        });

        return response()->json(['saved' => true]);
    }

    public function photo(StoryMilestone $milestone): StreamedResponse
    {
        abort_unless($milestone->our_story_id === 1 && $milestone->photo && Storage::disk('local')->exists($milestone->photo), 404);

        return Storage::disk('local')->response($milestone->photo, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
