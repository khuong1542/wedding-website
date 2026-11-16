<?php

namespace App\Services\Admin;

use App\Models\Wish;
use App\Repositories\Admin\WishRepository;
use Illuminate\Support\Facades\DB;

class WishService
{
    public function __construct(private WishRepository $repository) {}

    public function data(array $filters): array
    {
        $wishes = $this->repository->paginate($filters);
        $selected = ((isset($filters['selected']) && (int) $filters['selected'] === 0)) ? null : (! empty($filters['selected']) ? $this->repository->findOrFail((int) $filters['selected']) : $wishes->first());
        $counts = array_replace(array_fill_keys(Wish::STATUSES, 0), $this->repository->counts());

        return compact('wishes', 'selected', 'counts', 'filters') + ['total' => array_sum($counts), 'statuses' => Wish::STATUSES, 'settings' => $this->repository->settings()];
    }

    public function status(int $id, array $data): void
    {
        $this->repository->status($id, $data['status']);
    }

    public function bulk(array $data): void
    {
        DB::transaction(fn () => $this->repository->bulk($data['ids'], $data['action']));
    }

    public function settings(array $data): void
    {
        $this->repository->saveSettings($data);
    }
}
