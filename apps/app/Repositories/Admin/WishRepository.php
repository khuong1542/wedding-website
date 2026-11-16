<?php

namespace App\Repositories\Admin;

use App\Models\Wish;
use App\Models\WishSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Wish::query()->when($filters['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('message', 'like', '%'.$q.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('created_at', ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc')->orderBy('id', ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc')
            ->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function counts(): array
    {
        return Wish::query()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status')->all();
    }

    public function findOrFail(int $id): Wish
    {
        return Wish::findOrFail($id);
    }

    public function status(int $id, string $status): void
    {
        $wish = $this->findOrFail($id);
        $wish->status = $status;
        $wish->save();
    }

    public function bulk(array $ids, string $action): void
    {
        $query = Wish::whereIn('id', $ids);
        $query->update(['status' => $action === 'publish' ? 'published' : 'hidden', 'updated_at' => now()]);
    }

    public function settings(): WishSetting
    {
        return WishSetting::find(1) ?? new WishSetting(['is_visible' => true, 'allow_submissions' => true, 'require_approval' => true, 'display_limit' => 10, 'sort' => 'newest']);
    }

    public function saveSettings(array $data): void
    {
        WishSetting::updateOrCreate(['id' => 1], $data);
    }
}
