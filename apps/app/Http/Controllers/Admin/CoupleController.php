<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCoupleRequest;
use App\Models\CoupleProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CoupleController extends Controller
{
    public function update(UpdateCoupleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $newPaths = [];
        $oldPaths = [];
        try {
            foreach (CoupleProfile::IMAGE_FIELDS as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('couple', 'local');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store couple photo.');
                    }
                    $newPaths[$field] = $path;
                }
                unset($data[$field], $data['remove_'.$field]);
            }
            DB::transaction(function () use ($data, $newPaths, &$oldPaths, $request): void {
                // One couple profile belongs to this wedding website.
                DB::table('couple_profiles')->insertOrIgnore([
                    'id' => 1, 'details' => '{}', 'created_at' => now(), 'updated_at' => now(),
                ]);
                $profile = CoupleProfile::query()->lockForUpdate()->findOrFail(1);
                foreach (CoupleProfile::IMAGE_FIELDS as $field) {
                    if (isset($newPaths[$field]) || $request->boolean('remove_'.$field)) {
                        if ($profile->$field) {
                            $oldPaths[] = $profile->$field;
                        }
                        $profile->$field = $newPaths[$field] ?? null;
                    }
                }
                $profile->details = $data;
                $profile->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($newPaths));
            throw $exception;
        }
        Storage::disk('local')->delete($oldPaths);

        return redirect()->route('admin.dashboard', ['page' => 'wedding', 'lang' => app()->getLocale()])
            ->with('couple_saved', true);
    }

    public function photo(string $person, string $kind = 'photo'): StreamedResponse
    {
        abort_unless(in_array($person, ['groom', 'bride'], true), 404);
        abort_unless(in_array($kind, ['photo', 'bank_qr'], true), 404);
        $path = CoupleProfile::query()->findOrFail(1)->{$person.'_'.$kind};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
