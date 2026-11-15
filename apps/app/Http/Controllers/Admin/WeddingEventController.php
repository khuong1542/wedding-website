<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\ListWeddingEventsRequest;
use App\Http\Requests\Admin\SaveWeddingEventRequest;
use App\Http\Requests\Admin\UpdateEventVisibilityRequest;
use App\Services\Admin\WeddingEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WeddingEventController extends Controller
{
    public function __construct(private WeddingEventService $service) {}

    public function index(ListWeddingEventsRequest $request): View
    {
        return view('admin.pages.events.index', $this->service->data($request->validated()));
    }

    public function shared(ListWeddingEventsRequest $request): View
    {
        return view('admin.pages.events.index', $this->service->data($request->validated()));
    }

    public function save(SaveWeddingEventRequest $request): RedirectResponse
    {
        $event = $this->service->save($request->validated());

        return redirect()->route($event->side === 'shared' ? 'admin.shared-events.index' : 'admin.events.index', ['side' => $event->side, 'event' => $event->id, 'lang' => app()->getLocale()])->with('success', __('events.saved'));
    }

    public function visibility(UpdateEventVisibilityRequest $request, int $event): JsonResponse
    {
        $this->service->visibility($event, $request->validated());

        return response()->json(['saved' => true]);
    }

    public function destroy(AdminResourceRequest $request, int $event): RedirectResponse
    {
        $side = $this->service->destroy((int) $request->validated('resource_id'));

        return redirect()->route($side === 'shared' ? 'admin.shared-events.index' : 'admin.events.index', ['side' => $side, 'lang' => app()->getLocale()])->with('success', __('events.deleted'));
    }

    public function photo(AdminResourceRequest $request, int $event): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'));
    }
}
