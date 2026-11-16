<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveNavigationRequest;
use App\Http\Requests\Admin\ViewNavigationRequest;
use App\Services\Admin\AppearanceService;
use App\Services\Admin\NavigationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NavigationController extends Controller
{
    public function __construct(private NavigationService $service, private AppearanceService $appearance) {}

    public function index(ViewNavigationRequest $request): View
    {
        return view('admin.pages.navigation.index', $this->service->data());
    }

    public function save(SaveNavigationRequest $request): RedirectResponse
    {
        $this->service->save($request->validated());

        return redirect()->route('admin.navigation.index', ['lang' => app()->getLocale()])->with('success', __('navigation.saved'));
    }

    public function preview(SaveNavigationRequest $request): JsonResponse
    {
        return response()->json(['html' => view('admin.pages.navigation.preview', $this->service->preview($request->validated()))->render()])->header('Cache-Control', 'no-store, private');
    }

    public function logo(ViewNavigationRequest $request): StreamedResponse
    {
        return $this->service->logo();
    }

    public function show(ViewNavigationRequest $request): View
    {
        $preview = $this->appearance->preview();

        return view($preview['view'], $preview['data']);
    }
}
