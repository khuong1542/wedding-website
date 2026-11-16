<?php

namespace App\Services\Admin;

use App\Models\RsvpResponse;
use App\Repositories\Admin\RsvpResponseRepository;
use Illuminate\Support\Facades\DB;

class RsvpResponseService
{
    public function __construct(private RsvpResponseRepository $repository) {}

    public function data(array $filters): array
    {
        $counts = array_replace(array_fill_keys(RsvpResponse::STATUSES, 0), $this->repository->counts());
        $responses = $this->repository->paginate($filters);
        $selected = ! empty($filters['selected']) ? $this->repository->findOrFail((int) $filters['selected']) : null;

        return compact('counts', 'responses', 'selected', 'filters') + ['total' => array_sum($counts), 'groups' => RsvpResponse::GROUPS, 'statuses' => RsvpResponse::STATUSES];
    }

    public function update(int $id, array $data): void
    {
        DB::transaction(function () use ($id, $data) {
            $response = $this->repository->findOrFail($id, true);
            $data['attendees'] = $data['status'] === 'attending' ? $data['attendees'] : 0;
            $data['responded_at'] = $data['status'] === 'pending' ? null : ($response->responded_at ?? now());
            $this->repository->update($response, $data);
        });
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}
