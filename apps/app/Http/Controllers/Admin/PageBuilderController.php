<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewPageBuilderRequest;
use App\Http\Requests\Admin\SavePageBuilderRequest;
use App\Http\Requests\Admin\ViewPageBuilderRequest;
use App\Services\Admin\PageBuilderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class PageBuilderController extends Controller
{
    public function __construct(private PageBuilderService $service) {}

    public function index(ViewPageBuilderRequest $request): View
    {
        return view('admin.pages.page-builder.index', $this->service->data());
    }

    public function save(SavePageBuilderRequest $request): JsonResponse
    {
        $builder = $this->service->save($request->validated());

        return response()->json(['revision' => $builder->revision, 'message' => __('builder.saved'), 'published_at' => $builder->published_at?->toIso8601String()]);
    }

    public function preview(PreviewPageBuilderRequest $request): JsonResponse
    {
        $preview = $this->service->preview($request->validated('blocks'));

        return response()->json(['html' => view($preview['view'], $preview['data'])->render()])->header('Cache-Control', 'no-store, private');
    }

    public function show(ViewPageBuilderRequest $request): View
    {
        $preview = $this->service->preview(version: $request->validated('version', 'draft'));

        return view($preview['view'], $preview['data']);
    }
}
