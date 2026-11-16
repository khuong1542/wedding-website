<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Http\Requests\Admin\BulkGalleryRequest;
use App\Http\Requests\Admin\DownloadGalleryRequest;
use App\Http\Requests\Admin\ListGalleryRequest;
use App\Http\Requests\Admin\SaveGalleryAlbumRequest;
use App\Http\Requests\Admin\SaveGalleryMediaRequest;
use App\Services\Admin\GalleryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryController extends Controller
{
    public function __construct(private GalleryService $service) {}

    public function index(ListGalleryRequest $request): View
    {
        return view('admin.pages.gallery.index', $this->service->data($request->validated()));
    }

    public function save(SaveGalleryMediaRequest $request): RedirectResponse
    {
        $media = $this->service->save($request->validated());

        return redirect()->route('admin.gallery.index', ['selected' => $media->id, 'lang' => app()->getLocale()])->with('success', __('gallery.saved'));
    }

    public function album(SaveGalleryAlbumRequest $request): RedirectResponse
    {
        $id = $this->service->album($request->validated());

        return redirect()->route('admin.gallery.index', ['tab' => 'albums', 'edit_album' => $id, 'lang' => app()->getLocale()])->with('success', __('gallery.saved'));
    }

    public function deleteAlbum(AdminResourceRequest $request): RedirectResponse
    {
        $this->service->deleteAlbum((int) $request->validated('resource_id'));

        return redirect()->route('admin.gallery.index', ['tab' => 'albums', 'lang' => app()->getLocale()])->with('success', __('gallery.deleted'));
    }

    public function bulk(BulkGalleryRequest $request): RedirectResponse
    {
        $this->service->bulk($request->validated());

        return redirect()->route('admin.gallery.index', ['lang' => app()->getLocale()])->with('success', __('gallery.saved'));
    }

    public function photo(AdminResourceRequest $request): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'));
    }

    public function download(AdminResourceRequest $request): StreamedResponse
    {
        return $this->service->photo((int) $request->validated('resource_id'), true);
    }

    public function downloadSelected(DownloadGalleryRequest $request): BinaryFileResponse
    {
        return $this->service->downloadSelected($request->validated('ids'));
    }

    public function preview(AdminResourceRequest $request): View
    {
        return view('admin.pages.gallery.preview', $this->service->preview((int) $request->validated('resource_id')));
    }
}
