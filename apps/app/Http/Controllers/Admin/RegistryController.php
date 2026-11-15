<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRegistryRequest;
use App\Services\Admin\RegistryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegistryController extends Controller
{
    public function __construct(private RegistryService $service) {}

    public function index(): View
    {
        return view('admin.pages.registry.index', $this->service->data());
    }

    public function update(UpdateRegistryRequest $request): RedirectResponse
    {
        $this->service->update($request->validated());

        return redirect()->route('admin.registry.index', ['lang' => app()->getLocale()])->with('success', __('registry.saved'));
    }
}
