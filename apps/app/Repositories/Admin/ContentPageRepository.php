<?php

namespace App\Repositories\Admin;

use App\Models\ContentPage;

class ContentPageRepository
{
    public function primary(bool $lock = false): ?ContentPage
    {
        return ContentPage::query()->orderByRaw("CASE WHEN slug = '/' THEN 0 ELSE 1 END")
            ->orderBy('id')->when($lock, fn ($query) => $query->lockForUpdate())->first();
    }

    public function findOrFail(int $id, bool $lock = false): ContentPage
    {
        return ContentPage::query()->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function slugExists(string $slug): bool
    {
        return ContentPage::where('slug', $slug)->exists();
    }

    public function make(): ContentPage
    {
        return new ContentPage;
    }

    public function save(ContentPage $page, array $data): ContentPage
    {
        $page->fill($data)->save();

        return $page;
    }

    public function delete(ContentPage $page): void
    {
        $page->delete();
    }
}
