<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\ListContentPagesRequest;
use App\Http\Requests\Admin\PreviewContentPageRequest;
use App\Http\Requests\Admin\SaveContentPageRequest;
use App\Http\Requests\Admin\UpdateEventVisibilityRequest;
use App\Services\Admin\ContentPageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentPageController extends Controller
{
    public function __construct(private ContentPageService $service) {}

    public function index(ListContentPagesRequest $request): View
    {
        return view('admin.pages.content-pages.index', $this->service->data());
    }

    public function save(SaveContentPageRequest $request): RedirectResponse
    {
        $this->service->save($request->validated());

        return redirect()->route('admin.pages.index', ['lang' => app()->getLocale()])->with('success', __('pages.saved'));
    }

    public function preview(PreviewContentPageRequest $request): JsonResponse
    {
        $preview = $this->service->preview($request->validated());

        return response()->json(['html' => view($preview['view'], $preview['data'])->render()])->header('Cache-Control', 'no-store, private');
    }

    public function visibility(UpdateEventVisibilityRequest $request, int $contentPage): JsonResponse
    {
        $this->service->visibility($contentPage, $request->validated());

        return response()->json(['saved' => true]);
    }

    public function delete(AdminResourceRequest $request, int $contentPage): RedirectResponse
    {
        $this->service->delete((int) $request->validated('resource_id'));

        return redirect()->route('admin.pages.index', ['lang' => app()->getLocale()])->with('success', __('pages.deleted'));
    }

    public function photo(AdminResourceRequest $request, int $contentPage): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'));
    }
}
