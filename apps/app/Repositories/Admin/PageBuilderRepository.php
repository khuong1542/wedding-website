<?php

namespace App\Repositories\Admin;

use App\Models\PageBuilder;

class PageBuilderRepository
{
    public function current(bool $lock = false): ?PageBuilder
    {
        return PageBuilder::query()->when($lock, fn ($query) => $query->lockForUpdate())->find(1);
    }

    public function save(array $data): PageBuilder
    {
        $builder = $this->current() ?? new PageBuilder;
        $builder->id = 1;
        $builder->fill($data)->save();

        return $builder;
    }
}
