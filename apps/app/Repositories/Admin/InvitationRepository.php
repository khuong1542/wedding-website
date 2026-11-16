<?php

namespace App\Repositories\Admin;

use App\Models\Invitation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvitationRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Invitation::query()->when($filters['q'] ?? null, fn ($query, $q) => $query->where('title', 'like', '%'.$q.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));
        match ($filters['sort'] ?? 'newest') {
            'name' => $query->orderBy('title')->orderBy('id'), 'oldest' => $query->orderBy('id'), default => $query->orderByDesc('id')
        };

        return $query->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function counts(): array
    {
        return Invitation::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status')->all();
    }

    public function findOrFail(int $id, bool $lock = false): Invitation
    {
        return Invitation::query()->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function make(array $data = []): Invitation
    {
        return new Invitation($data);
    }

    public function save(Invitation $invitation, array $data): Invitation
    {
        $invitation->fill($data)->save();

        return $invitation;
    }

    public function delete(Invitation $invitation): void
    {
        $invitation->delete();
    }
}
