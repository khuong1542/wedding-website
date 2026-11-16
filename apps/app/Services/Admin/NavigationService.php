<?php

namespace App\Services\Admin;

use App\Repositories\Admin\NavigationRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class NavigationService
{
    public function __construct(private NavigationRepository $repository) {}

    public function data(): array
    {
        return ['settings' => $this->repository->data(), 'icons' => config('website_navigation.icons')];
    }

    public function save(array $data): void
    {
        $new = null;
        $old = null;
        try {
            if (isset($data['logo'])) {
                $new = $data['logo']->store('navigation', 'local');
                if ($new === false) {
                    throw new \RuntimeException('Unable to store footer logo.');
                }
            }
            DB::transaction(function () use ($data, $new, &$old) {
                $current = $this->repository->current(true);
                $attributes = Arr::only($data, ['menu', 'footer_links', 'footer', 'display']);
                if ($new || ! empty($data['remove_logo'])) {
                    $old = $current?->logo;
                    $attributes['logo'] = $new;
                }
                $this->repository->save($attributes);
            });
        } catch (Throwable $exception) {
            if ($new) {
                Storage::disk('local')->delete($new);
            } throw $exception;
        }
        if ($old) {
            Storage::disk('local')->delete($old);
        }
    }

    public function preview(?array $data = null): array
    {
        $settings = $data ?? $this->repository->data();
        $logo = null;
        if (isset($data['logo'])) {
            $logo = 'data:'.$data['logo']->getMimeType().';base64,'.base64_encode($data['logo']->getContent());
        } elseif (empty($data['remove_logo'])) {
            $path = $this->repository->current()?->logo;
            if ($path && Storage::disk('local')->exists($path)) {
                $logo = 'data:'.Storage::disk('local')->mimeType($path).';base64,'.base64_encode(Storage::disk('local')->get($path));
            }
        }

        return ['websiteNavigation' => $settings, 'footerLogo' => $logo];
    }

    public function logo(): StreamedResponse
    {
        $path = $this->repository->current()?->logo;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
