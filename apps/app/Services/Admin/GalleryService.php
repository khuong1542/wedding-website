<?php

namespace App\Services\Admin;

use App\Models\GalleryMedia;
use App\Repositories\Admin\GalleryRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GalleryService
{
    public function __construct(private GalleryRepository $repository) {}

    public function data(array $filters): array
    {
        $media = $this->repository->media($filters);
        $albums = $this->repository->albums();
        $counts = array_replace(['image' => 0, 'video' => 0], $this->repository->counts());
        $selected = ! empty($filters['new']) || (isset($filters['selected']) && (int) $filters['selected'] === 0) ? null : (! empty($filters['selected']) ? $this->repository->findMedia((int) $filters['selected']) : $media->first());
        $selectedAlbum = ! empty($filters['edit_album']) ? $this->repository->findAlbum((int) $filters['edit_album']) : null;

        return compact('media', 'albums', 'counts', 'selected', 'selectedAlbum', 'filters') + ['total' => array_sum($counts)];
    }

    public function save(array $data): GalleryMedia
    {
        $new = null;
        $old = null;
        try {
            if ($data['type'] === 'image' && isset($data['photo'])) {
                $new = $data['photo']->store('gallery', 'local');
                if ($new === false) {
                    throw new \RuntimeException('Unable to store gallery image.');
                }
            }
            $media = DB::transaction(function () use ($data, $new, &$old) {
                $media = ! empty($data['id']) ? $this->repository->findMedia((int) $data['id'], true) : null;
                $attributes = Arr::except($data, ['id', 'photo']);
                if ($data['type'] === 'image') {
                    if (! $new && ! $media?->photo) {
                        throw ValidationException::withMessages(['photo' => __('gallery.photo_required')]);
                    } $attributes['video_url'] = null;
                }
                if ($new || $data['type'] === 'video') {
                    $old = $media?->photo;
                    $attributes['photo'] = $new;
                }

                return $this->repository->saveMedia($media, $attributes);
            });
        } catch (Throwable $exception) {
            if ($new) {
                Storage::disk('local')->delete($new);
            } throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $media;
    }

    public function album(array $data): int
    {
        return $this->repository->saveAlbum(! empty($data['id']) ? (int) $data['id'] : null, Arr::except($data, ['id']))->id;
    }

    public function deleteAlbum(int $id): void
    {
        $this->repository->deleteAlbum($id);
    }

    public function bulk(array $data): void
    {
        $paths = DB::transaction(function () use ($data) {
            if ($data['action'] === 'delete') {
                $paths = [];
                foreach ($this->repository->selected($data['ids']) as $media) {
                    if ($media->photo) {
                        $paths[] = $media->photo;
                    } $this->repository->deleteMedia($media);
                }

                return $paths;
            }
            $attributes = $data['action'] === 'album' ? ['album_id' => $data['album_id']] : ['is_visible' => $data['action'] === 'show'];
            $this->repository->updateSelected($data['ids'], $attributes);

            return [];
        });
        if ($paths) {
            Storage::disk('local')->delete($paths);
        }
    }

    public function photo(int $id, bool $download = false): StreamedResponse
    {
        $media = $this->repository->findMedia($id);
        abort_unless($media->photo && Storage::disk('local')->exists($media->photo), 404);
        $name = Str::slug($media->title).'.'.pathinfo($media->photo, PATHINFO_EXTENSION);

        return Storage::disk('local')->response($media->photo, $download ? $name : null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'], $download ? 'attachment' : 'inline');
    }

    public function downloadSelected(array $ids): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'wedding-gallery-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create download archive.');
        }
        $zip = new \ZipArchive;
        try {
            if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to open download archive.');
            }
            foreach ($this->repository->forDownload($ids) as $media) {
                $name = $media->id.'-'.(Str::slug($media->title) ?: 'media');
                if ($media->photo && Storage::disk('local')->exists($media->photo)) {
                    $zip->addFile(Storage::disk('local')->path($media->photo), $name.'.'.pathinfo($media->photo, PATHINFO_EXTENSION));
                } elseif ($media->type === 'video') {
                    $zip->addFromString($name.'.txt', $media->title."\n".$media->video_url."\n");
                }
            }
            $empty = $zip->numFiles === 0;
            $zip->close();
            if ($empty) {
                throw ValidationException::withMessages(['ids' => __('gallery.empty')]);
            }
        } catch (Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }
            throw $exception;
        }

        return response()->download($path, 'wedding-gallery.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    public function preview(int $id): array
    {
        $media = $this->repository->findMedia($id);
        $embed = null;
        if ($media->type === 'video') {
            $host = strtolower(parse_url($media->video_url, PHP_URL_HOST) ?? '');
            $path = parse_url($media->video_url, PHP_URL_PATH) ?? '';
            parse_str(parse_url($media->video_url, PHP_URL_QUERY) ?? '', $query);
            $youtube = in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) ? ($query['v'] ?? (str_starts_with($path, '/shorts/') ? substr($path, 8) : null)) : ($host === 'youtu.be' ? ltrim($path, '/') : null);
            if (is_string($youtube) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube)) {
                $embed = 'https://www.youtube-nocookie.com/embed/'.$youtube;
            } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && preg_match('~^/[0-9]+$~', $path)) {
                $embed = 'https://player.vimeo.com/video'.$path;
            }
        }

        return compact('media', 'embed');
    }
}
