<?php

namespace App\Services\Admin;

use App\Models\Invitation;
use App\Repositories\Admin\InvitationRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InvitationService
{
    public function __construct(private InvitationRepository $repository) {}

    public function data(array $filters): array
    {
        $invitations = $this->repository->paginate($filters);
        $selected = ! empty($filters['new']) ? null : (! empty($filters['selected']) ? $this->repository->findOrFail((int) $filters['selected']) : $invitations->first());
        $counts = array_replace(array_fill_keys(Invitation::STATUSES, 0), $this->repository->counts());

        return compact('invitations', 'selected', 'counts', 'filters') + ['total' => array_sum($counts), 'templates' => config('invitation_templates'), 'statuses' => Invitation::STATUSES];
    }

    public function save(array $data): Invitation
    {
        $new = null;
        $old = null;
        try {
            if (isset($data['photo'])) {
                $new = $data['photo']->store('invitations', 'local');
                if ($new === false) {
                    throw new \RuntimeException('Unable to store invitation image.');
                }
            }
            $invitation = DB::transaction(function () use ($data, $new, &$old) {
                $invitation = ! empty($data['id']) ? $this->repository->findOrFail((int) $data['id'], true) : $this->repository->make();
                $attributes = Arr::except($data, ['id', 'photo', 'remove_photo']);
                if ($new || ! empty($data['remove_photo'])) {
                    $old = $invitation->photo;
                    $attributes['photo'] = $new;
                }

                return $this->repository->save($invitation, $attributes);
            });
        } catch (Throwable $exception) {
            if ($new) {
                Storage::disk('local')->delete($new);
            } throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $invitation;
    }

    public function duplicate(int $id): Invitation
    {
        $photo = null;
        try {
            return DB::transaction(function () use ($id, &$photo) {
                $source = $this->repository->findOrFail($id, true);
                if ($source->photo && Storage::disk('local')->exists($source->photo)) {
                    $photo = 'invitations/'.Str::uuid().'.'.pathinfo($source->photo, PATHINFO_EXTENSION);
                    if (! Storage::disk('local')->copy($source->photo, $photo)) {
                        throw new \RuntimeException('Unable to copy invitation image.');
                    }
                }
                $attributes = $source->only($source->getFillable());
                $attributes['title'] = Str::limit($source->title, 100, '').' — '.__('invitations.copy');
                $attributes['status'] = 'draft';
                $attributes['photo'] = $photo;

                return $this->repository->save($this->repository->make(), $attributes);
            });
        } catch (Throwable $exception) {
            if ($photo) {
                Storage::disk('local')->delete($photo);
            } throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $invitation = $this->repository->findOrFail($id);
        $this->repository->delete($invitation);
        if ($invitation->photo) {
            Storage::disk('local')->delete($invitation->photo);
        }
    }

    public function photo(int $id): StreamedResponse
    {
        $invitation = $this->repository->findOrFail($id);
        abort_unless($invitation->photo && Storage::disk('local')->exists($invitation->photo), 404);

        return Storage::disk('local')->response($invitation->photo, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function preview(array $data = [], ?int $id = null): array
    {
        $invitation = $id ? $this->repository->findOrFail($id) : $this->repository->make(Arr::except($data, ['id', 'photo', 'remove_photo']));
        $image = null;
        if (isset($data['photo'])) {
            $image = 'data:'.$data['photo']->getMimeType().';base64,'.base64_encode($data['photo']->getContent());
        } elseif (empty($data['remove_photo'])) {
            $path = $id ? $invitation->photo : (! empty($data['id']) ? $this->repository->findOrFail((int) $data['id'])->photo : null);
            if ($path && Storage::disk('local')->exists($path)) {
                $image = 'data:'.Storage::disk('local')->mimeType($path).';base64,'.base64_encode(Storage::disk('local')->get($path));
            }
        }

        return compact('invitation', 'image');
    }
}
