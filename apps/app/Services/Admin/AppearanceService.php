<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AppearanceRepository;
use App\Repositories\Admin\ContentPageRepository;

class AppearanceService
{
    public function __construct(private AppearanceRepository $repository, private ContentPageRepository $pages, private ContentPageService $pageService) {}

    public function data(): array
    {
        return ['settings' => $this->repository->settings(), 'themes' => config('appearance.themes'), 'fonts' => config('appearance.fonts')];
    }

    public function save(array $data): void
    {
        $this->repository->save($data);
    }

    public function preview(?array $data = null): array
    {
        $page = $this->pages->primary();
        $preview = $this->pageService->preview(['id' => $page?->id, 'blade_template' => $page?->blade_template ?? 'classic', 'title' => $page?->title, 'groom_name' => $page?->groom_name, 'bride_name' => $page?->bride_name, 'summary' => $page?->summary]);
        $preview['data']['appearance'] = $data ?? $this->repository->settings();

        return $preview;
    }
}
