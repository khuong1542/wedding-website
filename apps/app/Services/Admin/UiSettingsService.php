<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AppearanceRepository;

class UiSettingsService
{
    public function __construct(private AppearanceRepository $repository, private AppearanceService $appearance) {}

    public function data(): array
    {
        return $this->appearance->data();
    }

    public function save(array $data): void
    {
        $this->repository->save(array_replace($this->repository->settings(), $data));
    }

    public function preview(?array $data = null): array
    {
        return $this->appearance->preview($data);
    }
}
