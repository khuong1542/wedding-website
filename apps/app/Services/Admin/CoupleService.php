<?php

namespace App\Services\Admin;

use App\Repositories\Admin\CoupleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CoupleService
{
    public function __construct(private CoupleRepository $coupleRepository) {}

    public function data(): array
    {
        return ['couple' => $this->coupleRepository->find()];
    }

    public function update(array $validated): void
    {
        $data = $validated;
        $newPaths = [];
        $oldPaths = [];
        try {
            foreach (['groom_photo', 'bride_photo', 'groom_bank_qr', 'bride_bank_qr'] as $field) {
                if (isset($validated[$field])) {
                    $path = $validated[$field]->store('couple', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store couple photo.');
                    }
                    $newPaths[$field] = $path;
                }
                unset($data[$field], $data['remove_'.$field]);
            }
            DB::transaction(function () use ($data, $newPaths, &$oldPaths, $validated): void {
                // One couple profile belongs to this wedding website.
                $profile = $this->coupleRepository->lock();
                foreach (['groom_photo', 'bride_photo', 'groom_bank_qr', 'bride_bank_qr'] as $field) {
                    if (isset($newPaths[$field]) || ! empty($validated['remove_'.$field])) {
                        if ($profile->$field) {
                            $oldPaths[] = $profile->$field;
                        }
                        $profile->$field = $newPaths[$field] ?? null;
                    }
                }
                $profile->details = $data;
                $this->coupleRepository->save($profile);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($newPaths));
            throw $exception;
        }
        Storage::disk('local')->delete($oldPaths);

    }

    public function photo(string $person, string $kind = 'photo'): StreamedResponse
    {
        abort_unless(in_array($person, ['groom', 'bride'], true), 404);
        abort_unless(in_array($kind, ['photo', 'bank_qr'], true), 404);
        $path = $this->coupleRepository->findOrFail()->{$person.'_'.$kind};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
