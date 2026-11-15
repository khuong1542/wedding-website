<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\ReorderOurStoryRequest;
use App\Http\Requests\Admin\UpdateOurStoryRequest;
use App\Services\Admin\OurStoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OurStoryController extends Controller
{
    public function __construct(private OurStoryService $service) {}

    public function index(): View
    {
        return view('admin.pages.our-story.index', $this->service->data());
    }

    public function update(UpdateOurStoryRequest $request): RedirectResponse
    {
        $this->service->update($request->validated());

        return redirect()->route('admin.our-story.index', ['lang' => app()->getLocale()])->with('success', __('story.saved'));
    }

    public function reorder(ReorderOurStoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated());

        return response()->json(['saved' => true]);
    }

    public function photo(AdminResourceRequest $request, int $milestone): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'));
    }
}
