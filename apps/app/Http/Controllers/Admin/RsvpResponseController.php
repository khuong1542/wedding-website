<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\ListRsvpResponsesRequest;
use App\Http\Requests\Admin\UpdateRsvpResponseRequest;
use App\Services\Admin\RsvpResponseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RsvpResponseController extends Controller
{
    public function __construct(private RsvpResponseService $service) {}

    public function index(ListRsvpResponsesRequest $request): View
    {
        return view('admin.pages.rsvp.index', $this->service->data($request->validated()));
    }

    public function update(UpdateRsvpResponseRequest $request, int $response): RedirectResponse
    {
        $this->service->update($response, $request->validated());

        return redirect()->route('admin.rsvp.index', ['selected' => $response, 'lang' => app()->getLocale()])->with('success', __('rsvp.saved'));
    }

    public function destroy(AdminResourceRequest $request, int $response): RedirectResponse
    {
        $this->service->delete((int) $request->validated('resource_id'));

        return redirect()->route('admin.rsvp.index', ['lang' => app()->getLocale()])->with('success', __('rsvp.deleted'));
    }
}
