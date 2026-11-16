<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveUiSettingsRequest;
use App\Http\Requests\Admin\ViewAppearanceRequest;
use App\Services\Admin\UiSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UiSettingsController extends Controller
{
    public function __construct(private UiSettingsService $service) {}

    public function index(ViewAppearanceRequest $request): View
    {
        return view('admin.pages.ui-settings.index', $this->service->data());
    }

    public function save(SaveUiSettingsRequest $request): RedirectResponse
    {
        $this->service->save($request->validated());

        return redirect()->route('admin.ui-settings.index', ['lang' => app()->getLocale()])->with('success', __('ui-settings.saved'));
    }

    public function preview(SaveUiSettingsRequest $request): JsonResponse
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
