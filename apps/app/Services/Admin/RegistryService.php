<?php

namespace App\Services\Admin;

use App\Repositories\Admin\CoupleRepository;
use App\Repositories\Admin\RegistryRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RegistryService
{
    public function __construct(private RegistryRepository $registryRepository, private CoupleRepository $coupleRepository) {}

    public function data(): array
    {
        return ['registry' => $this->registryRepository->find(), 'couple' => $this->coupleRepository->find()];
    }

    public function update(array $data): void
    {
        $new = [];
        $old = [];
        try {
            foreach (['bride', 'groom'] as $person) {
                $field = $person.'_bank_qr';
                if (isset($data[$field])) {
                    $path = $data[$field]->store('couple', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store QR image.');
                    }
                    $new[$field] = $path;
                }
            }
            DB::transaction(function () use ($data, $new, &$old): void {
                $registry = $this->registryRepository->lock();
                $registry->settings = array_replace($registry->settings ?? [], Arr::except($data, ['gifts', 'bride_bank_qr', 'groom_bank_qr', 'remove_bride_bank_qr', 'remove_groom_bank_qr']));
                if (array_key_exists('gifts', $data)) {
                    $registry->gifts = array_values($data['gifts']);
                }
                $this->registryRepository->save($registry);
                $couple = $this->coupleRepository->lock();
                foreach (['bride', 'groom'] as $person) {
                    $field = $person.'_bank_qr';
                    if (isset($new[$field]) || ! empty($data['remove_'.$field])) {
                        if ($couple->$field) {
                            $old[] = $couple->$field;
                        }
                        $couple->$field = $new[$field] ?? null;
                    }
                }
                $this->coupleRepository->save($couple);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($new));
            throw $exception;
        }
        Storage::disk('local')->delete($old);

    }
}
