<?php

namespace App\Services\Admin;

use App\Models\WeddingEvent;
use App\Repositories\Admin\WeddingEventRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class WeddingEventService
{
    public function __construct(private WeddingEventRepository $weddingEventRepository) {}

    public function data(array $filters): array
    {
        $side = $filters['side'] ?? 'groom';
        $side = in_array($side, ['groom', 'bride', 'shared'], true) ? $side : 'groom';
        $events = $this->weddingEventRepository->forSide($side);
        $selectedEvent = null;
        if (empty($filters['new'])) {
            $eventId = $filters['event'] ?? null;
            $selectedEvent = $eventId !== null ? $events->firstWhere('id', (int) $eventId) : $events->first();
            abort_if($eventId !== null && ! $selectedEvent, 404);
        }

        return compact('events', 'side', 'selectedEvent') + ['isShared' => $side === 'shared', 'eventIndexRoute' => $side === 'shared' ? 'admin.shared-events.index' : 'admin.events.index', 'eventSaveRoute' => $side === 'shared' ? 'admin.shared-events.save' : 'admin.events.save'];
    }

    public function save(array $data): WeddingEvent
    {
        $path = null;
        $old = null;
        try {
            if (isset($data['photo'])) {
                $path = $data['photo']->store('wedding-events', 'local');
                if ($path === false) {
                    throw new \RuntimeException('Unable to store event photo.');
                }
            }
            $event = DB::transaction(function () use ($data, $path, &$old): WeddingEvent {
                $event = ! empty($data['id']) ? $this->weddingEventRepository->lock((int) $data['id']) : $this->weddingEventRepository->make();
                abort_if($event->exists && (($event->side === 'shared') !== ($data['side'] === 'shared')), 422);
                $event->fill(Arr::except($data, ['id', 'photo', 'remove_photo']));
                if ($path || ! empty($data['remove_photo'])) {
                    $old = $event->photo;
                    $event->photo = $path;
                }
                $this->weddingEventRepository->save($event);

                return $event;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $event;
    }

    public function visibility(int $id, array $data): void
    {
        $this->weddingEventRepository->update($this->weddingEventRepository->findOrFail($id), $data);
    }

    public function destroy(int $id): string
    {
        $event = $this->weddingEventRepository->findOrFail($id);
        $side = $event->side;
        $photo = $event->photo;
        $this->weddingEventRepository->delete($event);
        if ($photo) {
            Storage::disk('local')->delete($photo);
        }

        return $side;
    }

    public function photo(int $id): StreamedResponse
    {
        $event = $this->weddingEventRepository->findOrFail($id);
        abort_unless($event->photo && Storage::disk('local')->exists($event->photo), 404);

        return Storage::disk('local')->response($event->photo, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
