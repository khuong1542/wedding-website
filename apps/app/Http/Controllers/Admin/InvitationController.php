<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\ListInvitationsRequest;
use App\Http\Requests\Admin\SaveInvitationRequest;
use App\Services\Admin\InvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvitationController extends Controller
{
    public function __construct(private InvitationService $service) {}

    public function index(ListInvitationsRequest $request): View
    {
        return view('admin.pages.invitations.index', $this->service->data($request->validated()));
    }

    public function save(SaveInvitationRequest $request): RedirectResponse
    {
        $invitation = $this->service->save($request->validated());

        return redirect()->route('admin.invitations.index', ['selected' => $invitation->id, 'lang' => app()->getLocale()])->with('success', __('invitations.saved'));
    }

    public function duplicate(AdminResourceRequest $request): RedirectResponse
    {
        $invitation = $this->service->duplicate((int) $request->validated('resource_id'));

        return redirect()->route('admin.invitations.index', ['selected' => $invitation->id, 'lang' => app()->getLocale()])->with('success', __('invitations.duplicated'));
    }

    public function destroy(AdminResourceRequest $request): RedirectResponse
    {
        $this->service->delete((int) $request->validated('resource_id'));

        return redirect()->route('admin.invitations.index', ['lang' => app()->getLocale()])->with('success', __('invitations.deleted'));
    }

    public function photo(AdminResourceRequest $request): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'));
    }

    public function show(AdminResourceRequest $request): View
    {
        return view('admin.pages.invitations.preview', $this->service->preview(id: (int) $request->validated('resource_id')));
    }

    public function preview(SaveInvitationRequest $request): JsonResponse
    {
        return response()->json(['html' => view('admin.pages.invitations.preview', $this->service->preview($request->validated()))->render()])->header('Cache-Control', 'no-store, private');
    }
}
