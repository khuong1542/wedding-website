<?php

namespace App\Repositories\Admin;

use App\Models\RsvpResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RsvpResponseRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return RsvpResponse::query()
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['group'] ?? null, fn ($query, $group) => $query->where('group', $group))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('responded_at', $date))
            ->orderByDesc('responded_at')->orderByDesc('id')->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function counts(): array
    {
        return RsvpResponse::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status')->all();
    }

    public function findOrFail(int $id, bool $lock = false): RsvpResponse
    {
        return RsvpResponse::query()->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function update(RsvpResponse $response, array $data): void
    {
        $response->fill($data)->save();
    }

    public function delete(int $id): void
    {
        $this->findOrFail($id)->delete();
    }
}
