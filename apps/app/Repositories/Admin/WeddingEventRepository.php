<?php

namespace App\Repositories\Admin;

use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Collection;

class WeddingEventRepository
{
    public function forSide(string $side): Collection
    {
        return WeddingEvent::where('side', $side)->orderBy('date')->orderBy('start_time')->orderBy('id')->get();
    }

    public function findOrFail(int $id): WeddingEvent
    {
        return WeddingEvent::findOrFail($id);
    }

    public function lock(int $id): WeddingEvent
    {
        return WeddingEvent::query()->lockForUpdate()->findOrFail($id);
    }

    public function make(): WeddingEvent
    {
        return new WeddingEvent;
    }

    public function save(WeddingEvent $event): void
    {
        $event->save();
    }

    public function update(WeddingEvent $event, array $data): void
    {
        $event->update($data);
    }

    public function delete(WeddingEvent $event): void
    {
        $event->delete();
    }
}
