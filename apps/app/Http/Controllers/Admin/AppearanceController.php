<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveAppearanceRequest;
use App\Http\Requests\Admin\ViewAppearanceRequest;
use App\Services\Admin\AppearanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AppearanceController extends Controller
{
    public function __construct(private AppearanceService $service) {}

    public function index(ViewAppearanceRequest $request): View
    {
        return view('admin.pages.themes.index', $this->service->data());
    }

    public function save(SaveAppearanceRequest $request): RedirectResponse
    {
        $this->service->save($request->validated());

        return redirect()->route('admin.themes.index', ['lang' => app()->getLocale()])->with('success', __('appearance.saved'));
    }

    public function preview(SaveAppearanceRequest $request): JsonResponse
    {
        $preview = $this->service->preview($request->validated());

        return response()->json(['html' => view($preview['view'], $preview['data'])->render()])->header('Cache-Control', 'no-store, private');
    }

    public function show(ViewAppearanceRequest $request): View
    {
        $preview = $this->service->preview();

        return view($preview['view'], $preview['data']);
    }
}
