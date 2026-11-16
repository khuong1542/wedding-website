<?php

namespace App\Repositories\Admin;

use App\Models\GalleryAlbum;
use App\Models\GalleryMedia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GalleryRepository
{
    public function media(array $filters): LengthAwarePaginator
    {
        $type = in_array($filters['tab'] ?? '', ['image', 'video'], true) ? $filters['tab'] : ($filters['type'] ?? null);
        $query = GalleryMedia::with('album')->when($type, fn ($query, $type) => $query->where('type', $type))->when($filters['album'] ?? null, fn ($query, $album) => $query->where('album_id', $album))->when($filters['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$q.'%')->orWhere('description', 'like', '%'.$q.'%')));
        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('id'), 'position' => $query->orderBy('position')->orderBy('id'), default => $query->orderByDesc('id')
        };

        return $query->paginate($filters['per_page'] ?? 12)->withQueryString();
    }

    public function albums(): Collection
    {
        return GalleryAlbum::withCount('media')->orderBy('position')->orderBy('id')->get();
    }

    public function counts(): array
    {
        return GalleryMedia::selectRaw('type, COUNT(*) AS total')->groupBy('type')->pluck('total', 'type')->all();
    }

    public function findMedia(int $id, bool $lock = false): GalleryMedia
    {
        return GalleryMedia::query()->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function findAlbum(int $id): GalleryAlbum
    {
        return GalleryAlbum::findOrFail($id);
    }

    public function saveMedia(?GalleryMedia $media, array $data): GalleryMedia
    {
        $media ??= new GalleryMedia;
        $media->fill($data)->save();

        return $media;
    }

    public function saveAlbum(?int $id, array $data): GalleryAlbum
    {
        $album = $id ? $this->findAlbum($id) : new GalleryAlbum;
        $album->fill($data)->save();

        return $album;
    }

    public function selected(array $ids): Collection
    {
        return GalleryMedia::whereIn('id', $ids)->lockForUpdate()->get();
    }

    public function forDownload(array $ids): Collection
    {
        return GalleryMedia::whereIn('id', $ids)->orderBy('id')->get();
    }

    public function deleteMedia(GalleryMedia $media): void
    {
        $media->delete();
    }

    public function deleteAlbum(int $id): void
    {
        $this->findAlbum($id)->delete();
    }

    public function updateSelected(array $ids, array $data): void
    {
        GalleryMedia::whereIn('id', $ids)->update($data);
    }
}
