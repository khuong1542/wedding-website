<?php

namespace App\Services\Admin;

use App\Models\PageBuilder;
use App\Repositories\Admin\ContentPageRepository;
use App\Repositories\Admin\PageBuilderRepository;
use Illuminate\Support\Facades\DB;

class PageBuilderService
{
    public function __construct(private PageBuilderRepository $repository, private ContentPageRepository $pages, private AppearanceService $appearance) {}

    public function data(): array
    {
        $builder = $this->repository->current();

        return ['builder' => $builder, 'blocks' => $builder?->draft ?? [], 'blockTypes' => config('page_builder.blocks'), 'contentPage' => $this->pages->primary(), 'revision' => $builder?->revision ?? 0];
    }

    public function save(array $data): PageBuilder
    {
        return DB::transaction(function () use ($data) {
            $current = $this->repository->current(true);
            abort_if(($current?->revision ?? 0) !== (int) $data['revision'], 409, __('builder.conflict'));
            $attributes = ['draft' => array_values($data['blocks']), 'revision' => ($current?->revision ?? 0) + 1];
            if ($data['action'] === 'publish') {
                $attributes['published'] = $attributes['draft'];
                $attributes['published_at'] = now();
            }

            return $this->repository->save($attributes);
        });
    }

    public function preview(?array $blocks = null, string $version = 'draft'): array
    {
        $preview = $this->appearance->preview();
        if ($blocks === null) {
            $current = $this->repository->current();
            $blocks = $version === 'published' ? $current?->published : $current?->draft;
            if ($blocks === null) {
                return $preview;
            }
        }
        $preview['view'] = 'templates.pages.builder';
        $preview['data']['blocks'] = $blocks;

        return $preview;
    }
}
