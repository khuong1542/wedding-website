<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkWishesRequest;
use App\Http\Requests\Admin\ListWishesRequest;
use App\Http\Requests\Admin\SaveWishSettingsRequest;
use App\Http\Requests\Admin\UpdateWishStatusRequest;
use App\Services\Admin\WishService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WishController extends Controller
{
    public function __construct(private WishService $service) {}

    public function index(ListWishesRequest $request): View
    {
        return view('admin.pages.wishes.index', $this->service->data($request->validated()));
    }

    public function status(UpdateWishStatusRequest $request, int $wish): RedirectResponse
    {
        $this->service->status($wish, $request->validated());

        return redirect()->route('admin.wishes.index', ['selected' => $wish, 'lang' => app()->getLocale()])->with('success', __('wishes.saved'));
    }

    public function bulk(BulkWishesRequest $request): RedirectResponse
    {
        $this->service->bulk($request->validated());

        return redirect()->route('admin.wishes.index', ['lang' => app()->getLocale()])->with('success', __('wishes.updated'));
    }

    public function settings(SaveWishSettingsRequest $request): RedirectResponse
    {
        $this->service->settings($request->validated());

        return redirect()->route('admin.wishes.index', ['tab' => 'settings', 'lang' => app()->getLocale()])->with('success', __('wishes.saved'));
    }
}
