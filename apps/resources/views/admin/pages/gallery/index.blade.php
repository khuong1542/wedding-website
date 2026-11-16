@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/gallery.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/gallery.js')
@endpush
@section('description', __('gallery.description'))
@section('actions')
    <div class="gallery-heading-actions">
        <a class="gallery-button" href="{{ route('admin.themes.show', ['lang' => app()->getLocale()]) }}" target="_blank"
            rel="noopener"><x-admin::icon name="show" />{{ __('gallery.view_website') }}</a><a
            class="gallery-button is-primary"
            href="{{ route('admin.gallery.index', ['new' => 1, 'lang' => app()->getLocale()]) }}"><x-admin::icon
                name="plus" />{{ __('gallery.add') }}</a>
    </div>
@endsection
@section('content')
    <div class="gallery-page" data-gallery>
        <nav class="gallery-tabs" aria-label="{{ __('gallery.type') }}">
            @foreach (['all' => $total, 'image' => $counts['image'], 'video' => $counts['video'], 'albums' => $albums->count()] as $tab => $count)
                <a class="gallery-button {{ ($filters['tab'] ?? 'all') === $tab ? 'is-primary' : '' }}"
                    href="{{ route('admin.gallery.index', ['tab' => $tab, 'lang' => app()->getLocale()]) }}"
                    @if (($filters['tab'] ?? 'all') === $tab) aria-current="page" @endif><x-admin::icon
                        :name="[
                            'all' => 'images',
                            'image' => 'image',
                            'video' => 'play-circle',
                            'albums' => 'folder',
                        ][$tab]" />{{ __('gallery.' . $tab) }} ({{ $count }})</a>
            @endforeach
        </nav>
        <div class="gallery-stats">
            @foreach (['total' => $total, 'image' => $counts['image'], 'video' => $counts['video'], 'albums' => $albums->count()] as $type => $count)
                <article class="admin-panel gallery-stat gallery-stat-{{ $type }}"><span><x-admin::icon
                            :name="[
                                'total' => 'images',
                                'image' => 'image',
                                'video' => 'play-circle',
                                'albums' => 'folder',
                            ][$type]" /></span>
                    <div><strong>{{ $count }}</strong><small>{{ __('gallery.' . $type) }}</small></div>
                </article>
            @endforeach
        </div>
        @php($showEditor = ($filters['tab'] ?? '') === 'albums' || $selected || !empty($filters['new']) || $media->isEmpty())
        <div class="gallery-workspace {{ $showEditor ? 'has-editor' : '' }}">
            <section class="admin-panel gallery-list-panel">
                @if (($filters['tab'] ?? '') === 'albums')
                    <div class="gallery-album-heading">
                        <h2>{{ __('gallery.albums') }}</h2><a class="gallery-button"
                            href="{{ route('admin.gallery.index', ['tab' => 'albums', 'lang' => app()->getLocale()]) }}"><x-admin::icon
                                name="plus" />{{ __('gallery.create_album') }}</a>
                    </div>
                    <div class="gallery-grid gallery-album-grid">
                        @forelse ($albums as $album)
                            <article class="gallery-album-card"><a
                                    href="{{ route('admin.gallery.index', ['album' => $album->id, 'lang' => app()->getLocale()]) }}"><x-admin::icon
                                        name="folder" />
                                    <h3>{{ $album->title }}</h3>
                                    <p>{{ __('gallery.media_count', ['count' => $album->media_count]) }}</p>
                                </a><span
                                    class="gallery-badge">{{ __('gallery.' . ($album->is_visible ? 'show' : 'hide')) }}</span>
                                <div class="gallery-card-actions"><a class="gallery-button"
                                        href="{{ route('admin.gallery.index', ['tab' => 'albums', 'edit_album' => $album->id, 'lang' => app()->getLocale()]) }}">{{ __('gallery.edit_album') }}</a><button
                                        class="gallery-button" type="button"
                                        data-gallery-delete-album="{{ route('admin.gallery.albums.destroy', ['album' => $album->id, 'lang' => app()->getLocale()]) }}"
                                        data-name="{{ $album->title }}">{{ __('gallery.delete') }}</button></div>
                        </article>@empty<p class="gallery-empty">{{ __('gallery.empty_albums') }}</p>
                        @endforelse
                    </div>
                @else
                    <form class="gallery-filters" data-gallery-filters method="GET"
                        action="{{ route('admin.gallery.index') }}"><input type="hidden" name="lang"
                            value="{{ app()->getLocale() }}"><input type="hidden" name="tab"
                            value="{{ $filters['tab'] ?? 'all' }}"><input type="hidden" name="view"
                            value="{{ $filters['view'] ?? 'grid' }}"><input type="hidden" name="per_page"
                            value="{{ $media->perPage() }}"><label class="gallery-search"><x-admin::icon
                                name="search" /><span class="sr-only">{{ __('gallery.search') }}</span><input
                                name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('gallery.search') }}"
                                maxlength="120"></label><label><span
                                class="sr-only">{{ __('gallery.album') }}</span><select name="album">
                                <option value="">{{ __('gallery.all_albums') }}</option>
                                @foreach ($albums as $album)
                                    <option value="{{ $album->id }}" @selected((int) ($filters['album'] ?? 0) === $album->id)>{{ $album->title }}
                                    </option>
                                @endforeach
                            </select>
                        </label><label><span class="sr-only">{{ __('gallery.type') }}</span><select name="type">
                                <option value="">{{ __('gallery.all_types') }}</option>
                                @foreach (['image', 'video'] as $type)
                                    <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>
                                        {{ __('gallery.' . $type) }}</option>
                                @endforeach
                            </select>
                        </label><label><span class="sr-only">{{ __('gallery.position') }}</span><select name="sort">
                                @foreach (['newest', 'oldest', 'position'] as $sort)
                                    <option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>
                                        {{ __('gallery.' . $sort) }}</option>
                                @endforeach
                            </select>
                        </label><button class="sr-only" type="submit">{{ __('gallery.filter') }}</button>
                        <div class="gallery-view-toggle">
                            @foreach (['grid' => 'grid-alt', 'list' => 'list-ul'] as $view => $icon)
                                <a href="{{ route('admin.gallery.index', array_merge(request()->query(), ['view' => $view])) }}"
                                    aria-label="{{ __('gallery.' . $view) }}"
                                    @if (($filters['view'] ?? 'grid') === $view) aria-current="page" @endif><x-admin::icon
                                        :name="$icon" /></a>
                            @endforeach
                        </div>
                    </form>
                    <form id="gallery-bulk-form" method="POST"
                        action="{{ route('admin.gallery.bulk', ['lang' => app()->getLocale()]) }}">@csrf<div
                            class="gallery-grid {{ ($filters['view'] ?? '') === 'list' ? 'is-list' : '' }}">
                            @forelse ($media as $item)
                                <article
                                    class="gallery-media-card {{ $selected?->id === $item->id ? 'is-selected' : '' }}">
                                    <label class="gallery-checkbox"><input type="checkbox" name="ids[]"
                                            value="{{ $item->id }}" data-gallery-check
                                            aria-label="{{ __('gallery.select_all') }} — {{ $item->title }}"></label><a
                                        class="gallery-card-photo"
                                        href="{{ route('admin.gallery.index', array_merge(request()->except('new'), ['selected' => $item->id])) }}">
                                        @if ($item->type === 'image')
                                            <img src="{{ route('admin.gallery.photo', $item) }}"
                                            alt="{{ $item->alt_text ?: $item->title }}" loading="lazy">@else<span
                                                class="gallery-video-placeholder"><x-admin::icon
                                                    name="play-circle" />{{ __('gallery.video') }}</span>
                                        @endif
                                    </a>
                                    <div class="gallery-card-info">
                                        <h3>{{ $item->title }}</h3>
                                        <small>{{ $item->album?->title ?: __('gallery.no_album') }}</small><span
                                            class="gallery-badge">{{ __('gallery.' . ($item->is_visible ? 'show' : 'hide')) }}</span>
                                        <div class="gallery-card-actions"><a
                                                href="{{ route('admin.gallery.preview', ['media' => $item->id, 'lang' => app()->getLocale()]) }}"
                                                target="_blank" rel="noopener"
                                                aria-label="{{ __('gallery.preview') }} — {{ $item->title }}"><x-admin::icon
                                                    name="show" /></a>
                                            @if ($item->type === 'image')
                                                <a href="{{ route('admin.gallery.download', $item) }}"
                                                    aria-label="{{ __('gallery.download') }} — {{ $item->title }}"><x-admin::icon
                                                        name="download" /></a>
                                            @endif
                                            <button type="button" data-gallery-delete-media="{{ $item->id }}"
                                                data-name="{{ $item->title }}"
                                                aria-label="{{ __('gallery.delete') }} — {{ $item->title }}">
                                                <x-admin::icon name="trash" /></button>
                                        </div>
                                    </div>
                            </article>@empty<p class="gallery-empty">{{ __('gallery.empty') }}</p>
                            @endforelse
                        </div>
                    </form>
                    <div class="gallery-list-footer">
                        <div class="gallery-bulk-actions"><label class="gallery-selection-chip"><input type="checkbox"
                                    data-gallery-all aria-label="{{ __('gallery.select_all') }}"><span data-gallery-count
                                    data-label="{{ __('gallery.selected', ['count' => ':count']) }}"
                                    aria-live="polite">{{ __('gallery.selected', ['count' => 0]) }}</span></label><button
                                class="gallery-button" type="button" data-gallery-bulk-delete data-gallery-bulk
                                disabled><x-admin::icon name="trash" />{{ __('gallery.delete') }}</button><button
                                class="gallery-button" type="button" data-gallery-assign data-gallery-bulk
                                @disabled($albums->isEmpty())><x-admin::icon
                                    name="folder" />{{ __('gallery.assign') }}</button><button class="gallery-button"
                                form="gallery-bulk-form" formaction="{{ route('admin.gallery.download-selected') }}"
                                type="submit" data-gallery-bulk disabled><x-admin::icon
                                    name="download" />{{ __('gallery.download') }}</button></div>
                        <div class="gallery-pagination">
                            <span
                                class="sr-only">{{ __('gallery.showing', ['from' => $media->firstItem() ?? 0, 'to' => $media->lastItem() ?? 0, 'total' => $media->total()]) }}</span>
                            <form method="GET" action="{{ route('admin.gallery.index') }}">
                                @foreach (request()->except(['page', 'per_page', 'selected', 'new']) as $key => $value)
                                    @if (is_scalar($value))
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <label>
                                    <span class="sr-only">{{ __('gallery.per_page', ['count' => '']) }}</span><select
                                        name="per_page" data-gallery-per-page>
                                        @foreach ([12, 24, 48] as $size)
                                            <option value="{{ $size }}" @selected($media->perPage() === $size)>
                                                {{ __('gallery.per_page', ['count' => $size]) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </form>
                            <nav aria-label="{{ __('gallery.all') }}">
                                <a class="gallery-button {{ $media->onFirstPage() ? 'is-disabled' : '' }}"
                                    @if ($media->previousPageUrl()) href="{{ $media->previousPageUrl() }}" @else aria-disabled="true" @endif
                                    aria-label="{{ __('gallery.previous') }}">‹</a>
                                @php($previousPageNumber = 0)
                                @foreach (array_unique(array_filter([1, $media->currentPage() - 1, $media->currentPage(), $media->currentPage() + 1, $media->currentPage() === 1 ? 3 : 0, $media->lastPage()], fn($number) => $number >= 1 && $number <= $media->lastPage())) as $pageNumber)
                                    @if ($previousPageNumber && $pageNumber - $previousPageNumber > 1)
                                        <span aria-hidden="true">…</span>
                                    @endif
                                    <a class="gallery-button {{ $pageNumber === $media->currentPage() ? 'is-primary' : '' }}"
                                        href="{{ $media->url($pageNumber) }}"
                                        @if ($pageNumber === $media->currentPage()) aria-current="page" @endif>{{ $pageNumber }}</a>
                                    @php($previousPageNumber = $pageNumber)
                                @endforeach
                                <a class="gallery-button {{ !$media->hasMorePages() ? 'is-disabled' : '' }}"
                                    @if ($media->nextPageUrl()) href="{{ $media->nextPageUrl() }}" @else aria-disabled="true" @endif
                                    aria-label="{{ __('gallery.next') }}">›</a>
                            </nav>
                        </div>
                    </div>
                @endif
            </section>
            @if ($showEditor)
                <aside class="admin-panel gallery-editor">
                    @if (($filters['tab'] ?? '') === 'albums')
                        <header>
                            <h2>{{ __('gallery.' . ($selectedAlbum ? 'edit_album' : 'create_album')) }}</h2>
                        </header>
                        <form method="POST"
                            action="{{ route('admin.gallery.albums.save', ['lang' => app()->getLocale()]) }}">@csrf<input
                                type="hidden" name="id" value="{{ $selectedAlbum?->id }}">
                            <div class="gallery-editor-fields">@include('admin.pages.gallery.album-form')</div>
                            <div class="gallery-editor-actions"><button type="submit"
                                    class="gallery-button is-primary">{{ __('gallery.save') }}</button></div>
                        </form>
                    @else
                        <header>
                            <h2><x-admin::icon name="edit" />{{ __('gallery.' . ($selected ? 'edit' : 'add')) }}</h2>
                            <a class="gallery-editor-close"
                                href="{{ route('admin.gallery.index', array_merge(request()->except('new'), ['selected' => 0])) }}"
                                aria-label="{{ __('gallery.close') }}"><x-admin::icon name="x" /></a>
                        </header>
                        <form method="POST" enctype="multipart/form-data"
                            action="{{ route('admin.gallery.save', ['lang' => app()->getLocale()]) }}" data-gallery-form>
                            @csrf<input type="hidden" name="id" value="{{ $selected?->id }}">
                            <div class="gallery-editor-fields">@include('admin.pages.gallery.media-form')</div>
                            <div class="gallery-editor-actions"><button type="reset"
                                    class="gallery-button">{{ __('gallery.cancel') }}</button><button type="submit"
                                    class="gallery-button is-primary"><x-admin::icon
                                        name="save" />{{ __('gallery.save') }}</button></div>
                        </form>
                    @endif
                </aside>
            @endif
        </div>
        <dialog class="gallery-dialog" data-gallery-album-dialog aria-labelledby="gallery-assign-title">
            <form method="POST" action="{{ route('admin.gallery.bulk', ['lang' => app()->getLocale()]) }}">@csrf<input
                    type="hidden" name="action" value="album">
                <h2 id="gallery-assign-title">{{ __('gallery.assign') }}</h2>
                <div data-gallery-album-ids></div>
                <div class="gallery-field"><label for="gallery-assign-album">{{ __('gallery.album') }}</label><select
                        id="gallery-assign-album" name="album_id" required>
                        <option value="">{{ __('gallery.album') }}</option>
                        @foreach ($albums as $album)
                            <option value="{{ $album->id }}">{{ $album->title }}</option>
                        @endforeach
                    </select></div>
                <div class="gallery-editor-actions"><button class="gallery-button" type="button"
                        data-gallery-close-album>{{ __('gallery.cancel') }}</button><button
                        class="gallery-button is-primary" type="submit">{{ __('gallery.assign') }}</button></div>
            </form>
        </dialog>
        <dialog class="gallery-dialog" data-gallery-confirm aria-labelledby="gallery-confirm-title">
            <form method="POST" data-gallery-delete-form>@csrf<input type="hidden" name="_method" value="POST">
                <h2 id="gallery-confirm-title">{{ __('gallery.delete') }}</h2>
                <p data-gallery-confirm-text data-media-text="{{ __('gallery.confirm_media') }}"
                    data-album-text="{{ __('gallery.confirm_album') }}"></p><strong data-gallery-delete-name></strong>
                <div data-gallery-delete-ids></div>
                <div class="gallery-editor-actions"><button class="gallery-button" type="button"
                        data-gallery-close>{{ __('gallery.close') }}</button><button class="gallery-button is-primary"
                        type="submit">{{ __('gallery.delete') }}</button></div>
            </form>
        </dialog>
    </div>
@endsection
