<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRegistryRequest;
use App\Models\CoupleProfile;
use App\Models\WeddingRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RegistryController extends Controller
{
    public function update(UpdateRegistryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $new = [];
        $old = [];
        try {
            foreach (['bride', 'groom'] as $person) {
                $field = $person.'_bank_qr';
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('couple', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store QR image.');
                    }
                    $new[$field] = $path;
                }
            }
            DB::transaction(function () use ($request, $data, $new, &$old): void {
                DB::table('wedding_registries')->insertOrIgnore(['id' => 1, 'settings' => '{}', 'gifts' => '[]', 'created_at' => now(), 'updated_at' => now()]);
                $registry = WeddingRegistry::query()->lockForUpdate()->findOrFail(1);
                $registry->settings = array_replace($registry->settings ?? [], Arr::except($data, ['gifts', 'bride_bank_qr', 'groom_bank_qr', 'remove_bride_bank_qr', 'remove_groom_bank_qr']));
                if (array_key_exists('gifts', $data)) {
                    $registry->gifts = array_values($data['gifts']);
                }
                $registry->save();
                DB::table('couple_profiles')->insertOrIgnore(['id' => 1, 'details' => '{}', 'created_at' => now(), 'updated_at' => now()]);
                $couple = CoupleProfile::query()->lockForUpdate()->findOrFail(1);
                foreach (['bride', 'groom'] as $person) {
                    $field = $person.'_bank_qr';
                    if (isset($new[$field]) || $request->boolean('remove_'.$field)) {
                        if ($couple->$field) {
                            $old[] = $couple->$field;
                        }
                        $couple->$field = $new[$field] ?? null;
                    }
                }
                $couple->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($new));
            throw $exception;
        }
        Storage::disk('local')->delete($old);

        return redirect()->route('admin.dashboard', ['page' => 'registry', 'lang' => app()->getLocale()])->with('success', __('registry.saved'));
    }
}
