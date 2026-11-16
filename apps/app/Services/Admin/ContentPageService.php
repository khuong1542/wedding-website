<?php

namespace App\Services\Admin;

use App\Models\ContentPage;
use App\Repositories\Admin\AppearanceRepository;
use App\Repositories\Admin\ContentPageRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ContentPageService
{
    public function __construct(private ContentPageRepository $repository, private AppearanceRepository $appearance) {}

    public function data(): array
    {
        return ['selectedPage' => $this->repository->primary(), 'pageTemplates' => config('page_templates')];
    }

    public function save(array $data): ContentPage
    {
        $new = null;
        $old = null;
        try {
            if (isset($data['photo'])) {
                $new = $data['photo']->store('content-pages', 'local');
                if ($new === false) {
                    throw new \RuntimeException('Unable to store page photo.');
                }
            }
            $page = DB::transaction(function () use ($data, $new, &$old): ContentPage {
                $page = $this->repository->primary(true) ?? $this->repository->make();
                $attributes = Arr::except($data, ['id', 'photo', 'remove_photo']);
                if (! $page->exists) {
                    $attributes['sections'] = [];
                    $attributes['is_visible'] ??= true;
                    if (! isset($attributes['slug'])) {
                        $base = rtrim(Str::limit(Str::slug($attributes['title']), 130, ''), '-') ?: 'page';
                        do {
                            $slug = '/'.$base.'-'.Str::lower(Str::random(8));
                        } while ($this->repository->slugExists($slug));
                        $attributes['slug'] = $slug;
                    }
                }
                if ($new || ! empty($data['remove_photo'])) {
                    $old = $page->photo;
                    $attributes['photo'] = $new;
                }

                return $this->repository->save($page, $attributes);
            });
        } catch (Throwable $exception) {
            if ($new) {
                Storage::disk('local')->delete($new);
            }
            throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $page;
    }

    public function preview(array $data): array
    {
        $definition = config('page_templates')[$data['blade_template']] ?? null;
        abort_unless($definition && view()->exists($definition['view']), 422);
        $image = null;
        if (isset($data['photo'])) {
            $image = 'data:'.$data['photo']->getMimeType().';base64,'.base64_encode($data['photo']->getContent());
        } elseif (empty($data['remove_photo']) && ! empty($data['id'])) {
            $page = $this->repository->findOrFail((int) $data['id']);
            if ($page->photo && Storage::disk('local')->exists($page->photo)) {
                $image = 'data:'.Storage::disk('local')->mimeType($page->photo).';base64,'.base64_encode(Storage::disk('local')->get($page->photo));
            }
        }

        return ['view' => $definition['view'], 'data' => [
            'appearance' => $this->appearance->settings(),
            'groomName' => filled($data['groom_name'] ?? null) ? $data['groom_name'] : __('pages.groom_name_placeholder'),
            'brideName' => filled($data['bride_name'] ?? null) ? $data['bride_name'] : __('pages.bride_name_placeholder'),
            'pageTitle' => filled($data['title'] ?? null) ? $data['title'] : __('pages.preview_sample_title'),
            'summary' => filled($data['summary'] ?? null) ? $data['summary'] : __('pages.preview_sample_summary'), 'image' => $image,
            'seoTitle' => $data['seo_title'] ?? '', 'seoDescription' => $data['seo_description'] ?? '',
        ]];
    }

    public function visibility(int $id, array $data): void
    {
        $this->repository->save($this->repository->findOrFail($id), $data);
    }

    public function delete(int $id): void
    {
        $page = $this->repository->findOrFail($id);
        $this->repository->delete($page);
        if ($page->photo) {
            Storage::disk('local')->delete($page->photo);
        }
    }

    public function photo(int $id): StreamedResponse
    {
        $page = $this->repository->findOrFail($id);
        abort_unless($page->photo && Storage::disk('local')->exists($page->photo), 404);

        return Storage::disk('local')->response($page->photo, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
