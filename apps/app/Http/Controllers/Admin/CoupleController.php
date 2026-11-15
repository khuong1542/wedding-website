<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouplePhotoRequest;
use App\Http\Requests\Admin\UpdateCoupleRequest;
use App\Services\Admin\CoupleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoupleController extends Controller
{
    public function __construct(private CoupleService $service) {}

    public function index(): View
    {
        return view('admin.pages.couple', $this->service->data());
    }

    public function update(UpdateCoupleRequest $request): RedirectResponse
    {
        $this->service->update($request->validated());

        return redirect()->route('admin.couple.index', ['lang' => app()->getLocale()])->with('couple_saved', true);
    }

    public function photo(CouplePhotoRequest $request, string $person, string $kind = 'photo'): StreamedResponse
    {
        return $this->service->photo($request->validated('person'), $request->validated('kind'));
    }
}
