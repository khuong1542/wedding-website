<?php

namespace App\Services\Admin;

use App\Repositories\Admin\OurStoryRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OurStoryService
{
    public function __construct(private OurStoryRepository $ourStoryRepository) {}

    public function data(): array
    {
        return ['story' => $this->ourStoryRepository->find()];
    }

    public function update(array $data): void
    {
        $newPaths = [];
        $oldPaths = [];
        try {
            foreach (array_keys($data['milestones'] ?? []) as $key) {
                if (isset($data['milestones'][$key]['photo'])) {
                    $path = $data['milestones'][$key]['photo']->store('our-story', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store story photo.');
                    }
                    $newPaths[$key] = $path;
                }
            }
            DB::transaction(function () use ($data, $newPaths, &$oldPaths): void {
                $story = $this->ourStoryRepository->lock(true);
                $this->ourStoryRepository->update($story, Arr::only($data, ['title', 'introduction']));
                $existing = $this->ourStoryRepository->milestones($story);
                $kept = [];
                foreach (array_keys($data['milestones'] ?? []) as $position => $key) {
                    $item = $data['milestones'][$key];
                    $milestone = ! empty($item['id']) ? $existing->get($item['id']) : $this->ourStoryRepository->makeMilestone($story);
                    abort_unless($milestone, 422);
                    $milestone->fill(Arr::only($item, ['title', 'date', 'description', 'is_visible']));
                    $milestone->position = $position;
                    if (isset($newPaths[$key]) || ! empty($item['remove_photo'])) {
                        if ($milestone->photo) {
                            $oldPaths[] = $milestone->photo;
                        }
                        $milestone->photo = $newPaths[$key] ?? null;
                    }
                    $this->ourStoryRepository->saveMilestone($milestone);
                    $kept[] = $milestone->id;
                }
                foreach ($existing as $milestone) {
                    if (! in_array($milestone->id, $kept, true)) {
                        if ($milestone->photo) {
                            $oldPaths[] = $milestone->photo;
                        }
                        $this->ourStoryRepository->deleteMilestone($milestone);
                    }
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($newPaths));
            throw $exception;
        }
        Storage::disk('local')->delete($oldPaths);

    }

    public function reorder(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $story = $this->ourStoryRepository->lock();
            $current = $this->ourStoryRepository->ids($story);
            $requested = array_map('intval', $data['ids']);
            $sorted = $requested;
            sort($sorted);
            abort_unless($current === $sorted, 422);
            foreach ($requested as $position => $id) {
                $this->ourStoryRepository->setPosition($story, $id, $position);
            }
        });

    }

    public function photo(int $id): StreamedResponse
    {
        $milestone = $this->ourStoryRepository->photoMilestone($id);
        abort_unless($milestone->our_story_id === 1 && $milestone->photo && Storage::disk('local')->exists($milestone->photo), 404);

        return Storage::disk('local')->response($milestone->photo, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
