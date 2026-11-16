@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/invitations.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/invitations.js')
@endpush
@section('description', __('invitations.description'))
@section('actions')
    <a class="iv-button is-primary" href="{{ route('admin.invitations.index', ['new' => 1, 'lang' => app()->getLocale()]) }}">
        <x-admin::icon name="plus" />{{ __('invitations.create') }}</a>
@endsection
@section('content')
    <div class="iv-page" data-invitations data-error="{{ __('invitations.error') }}">
        <div class="iv-stats">
            @foreach (['total', ...$statuses] as $status)
                <article class="admin-panel iv-stat iv-{{ $status }}"><span class="iv-stat-icon"><x-admin::icon
                            :name="[
                                'total' => 'layers',
                                'active' => 'check-circle',
                                'draft' => 'time',
                                'archived' => 'archive',
                            ][$status]" /></span>
                    <div>
                        <strong>{{ $status === 'total' ? $total : $counts[$status] }}</strong><span>{{ __('invitations.' . $status) }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <nav class="iv-tabs" aria-label="{{ __('invitations.list') }}">
            @foreach (['list', 'templates'] as $tab)
                <a class="iv-button {{ ($filters['tab'] ?? 'list') === $tab ? 'is-primary' : '' }}"
                    href="{{ route('admin.invitations.index', ['tab' => $tab, 'lang' => app()->getLocale()]) }}"
                    @if (($filters['tab'] ?? 'list') === $tab) aria-current="page" @endif><x-admin::icon
                        :name="$tab === 'list' ? 'envelope' : 'layers'" />{{ __('invitations.' . $tab) }}{{ $tab === 'templates' ? ' (' . count($templates) . ')' : '' }}</a>
            @endforeach
        </nav>
        <div class="iv-workspace">
            <section class="admin-panel iv-list">
                @if (($filters['tab'] ?? 'list') === 'templates')
                    <div class="iv-card-grid">
                        @foreach ($templates as $key => $definition)
                            <article class="iv-card">@include('admin.pages.invitations.art', [
                                'invitation' => (object) [
                                    'template' => $key,
                                    'accent' => '#D81B60',
                                    'groom_name' => null,
                                    'bride_name' => null,
                                    'date' => null,
                                    'time' => null,
                                    'venue' => null,
                                ],
                                'image' => null,
                            ])<h3>
                                    {{ __('invitations.' . ($key === 'photo' ? 'photo_template' : $key)) }}</h3><a
                                    class="iv-button"
                                    href="{{ route('admin.invitations.index', ['new' => 1, 'template' => $key, 'lang' => app()->getLocale()]) }}">{{ __('invitations.use') }}</a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <form class="iv-filters" method="GET" action="{{ route('admin.invitations.index') }}"><input
                            type="hidden" name="lang" value="{{ app()->getLocale() }}"><label class="iv-search"><span
                                class="sr-only">{{ __('invitations.search') }}</span><input name="q"
                                placeholder="{{ __('invitations.search') }}" value="{{ $filters['q'] ?? '' }}"
                                maxlength="120"></label><label><span
                                class="sr-only">{{ __('invitations.status') }}</span><select name="status">
                                <option value="">{{ __('invitations.all_statuses') }}</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ __('invitations.' . $status) }}</option>
                                @endforeach
                            </select>
                        </label><label><span class="sr-only">{{ __('invitations.newest') }}</span><select name="sort">
                                @foreach (['newest', 'oldest', 'name'] as $sort)
                                    <option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>
                                        {{ __('invitations.' . ($sort === 'name' ? 'name_sort' : $sort)) }}</option>
                                @endforeach
                            </select>
                        </label><input type="hidden" name="per_page" value="{{ $invitations->perPage() }}"><button
                            class="iv-button" type="submit"><x-admin::icon
                                name="filter" />{{ __('invitations.filter') }}</button></form>
                    <div class="iv-card-grid">
                        @forelse ($invitations as $invitation)
                            <article class="iv-card {{ $selected?->id === $invitation->id ? 'is-selected' : '' }}">
                                @include('admin.pages.invitations.art', [
                                    'image' => $invitation->photo
                                        ? route('admin.invitations.photo', $invitation)
                                        : null,
                                ])<div class="iv-card-heading">
                                    <h3>{{ $invitation->title }}</h3><span
                                        class="iv-badge iv-{{ $invitation->status }}">{{ __('invitations.' . $invitation->status) }}</span>
                                </div>
                                <div class="iv-card-actions"><button type="button"
                                        data-iv-preview-url="{{ route('admin.invitations.show', ['invitation' => $invitation->id, 'lang' => app()->getLocale()]) }}"><x-admin::icon
                                            name="show" />{{ __('invitations.preview') }}</button><a
                                        href="{{ route('admin.invitations.index', array_merge(request()->query(), ['selected' => $invitation->id, 'new' => 0])) }}"><x-admin::icon
                                            name="edit" />{{ __('invitations.edit_action') }}</a>
                                    <form method="POST"
                                        action="{{ route('admin.invitations.duplicate', ['invitation' => $invitation->id, 'lang' => app()->getLocale()]) }}">
                                        @csrf<button type="submit"><x-admin::icon
                                                name="copy" />{{ __('invitations.duplicate') }}</button></form><button
                                        type="button"
                                        data-iv-delete="{{ route('admin.invitations.destroy', ['invitation' => $invitation->id, 'lang' => app()->getLocale()]) }}"
                                        data-name="{{ $invitation->title }}"
                                        aria-label="{{ __('invitations.delete') }} — {{ $invitation->title }}"><x-admin::icon
                                            name="trash" /></button>
                                </div>
                            </article>
                        @empty
                            <div class="iv-empty"><x-admin::icon name="envelope" />
                                <p>{{ __('invitations.empty') }}</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="iv-pagination">
                        <span>{{ __('invitations.showing', ['from' => $invitations->firstItem() ?? 0, 'to' => $invitations->lastItem() ?? 0, 'total' => $invitations->total()]) }}</span>
                        <form method="GET" action="{{ route('admin.invitations.index') }}">
                            @foreach (request()->except(['page', 'per_page', 'selected', 'new']) as $key => $value)
                                @if (is_scalar($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label>
                                <span class="sr-only">{{ __('invitations.per_page', ['count' => '']) }}</span><select
                                    name="per_page" data-iv-per-page>
                                    @foreach ([10, 20, 50] as $size)
                                        <option value="{{ $size }}" @selected($invitations->perPage() === $size)>
                                            {{ __('invitations.per_page', ['count' => $size]) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </form>
                        <nav aria-label="{{ __('invitations.list') }}">
                            @if ($invitations->previousPageUrl())
                                <a class="iv-button" href="{{ $invitations->previousPageUrl() }}"
                                    aria-label="{{ __('invitations.previous') }}">‹</a>
                            @endif
                            <span>
                                {{ $invitations->currentPage() }} / {{ $invitations->lastPage() }}</span>
                            @if ($invitations->nextPageUrl())
                                <a class="iv-button" href="{{ $invitations->nextPageUrl() }}"
                                    aria-label="{{ __('invitations.next') }}">›</a>
                            @endif
                        </nav>
                    </div>
                @endif
            </section>
            <section class="admin-panel iv-editor">
                <h2><x-admin::icon name="file" />{{ __('invitations.' . ($selected ? 'edit' : 'create')) }}</h2>
                <form data-iv-form method="POST" enctype="multipart/form-data"
                    action="{{ route('admin.invitations.save', ['lang' => app()->getLocale()]) }}"
                    data-preview-url="{{ route('admin.invitations.preview') }}">@csrf<input type="hidden" name="id"
                        value="{{ $selected?->id }}">
                    <div class="iv-editor-tabs" role="tablist">
                        @foreach (['general', 'content', 'design', 'seo'] as $tab)
                            <button type="button" role="tab" id="iv-tab-{{ $tab }}"
                                aria-controls="iv-panel-{{ $tab }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                tabindex="{{ $loop->first ? 0 : -1 }}"
                                data-iv-tab="{{ $tab }}">{{ __('invitations.' . $tab) }}</button>
                        @endforeach
                    </div>
                    <div class="iv-panel" role="tabpanel" id="iv-panel-general" aria-labelledby="iv-tab-general"
                        data-iv-panel="general">
                        <div class="iv-field"><label for="iv-title">{{ __('invitations.title') }} *</label><input
                                id="iv-title" name="title" value="{{ old('title', $selected?->title) }}"
                                maxlength="120" required></div>
                        <div class="iv-field"><label for="iv-status">{{ __('invitations.status') }}</label><select
                                id="iv-status" name="status">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $selected?->status ?? 'draft') === $status)>
                                        {{ __('invitations.' . $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="iv-field"><label
                                for="iv-description">{{ __('invitations.description_field') }}</label>
                            <textarea id="iv-description" name="description" rows="3" maxlength="3000">{{ old('description', $selected?->description) }}</textarea>
                        </div>
                        <div class="iv-field"><label>{{ __('invitations.photo') }}</label>
                            <div class="iv-photo"><label class="iv-photo-frame" for="iv-photo"><span
                                        data-iv-photo-hint><x-admin::icon
                                            name="camera-plus" />{{ __('invitations.photo_hint') }}</span><img
                                        data-iv-photo-image alt="{{ __('invitations.photo') }}"
                                        @if ($selected?->photo && !old('remove_photo')) src="{{ route('admin.invitations.photo', $selected) }}" @else hidden @endif><input
                                        id="iv-photo" name="photo" type="file"
                                        accept="image/jpeg,image/png,image/webp"></label><input type="hidden"
                                    name="remove_photo" value="{{ old('remove_photo', 0) }}"><button type="button"
                                    data-iv-remove-photo aria-label="{{ __('invitations.remove_photo') }}"><x-admin::icon
                                        name="x" /></button></div>
                        </div>
                    </div>
                    <div class="iv-panel" role="tabpanel" id="iv-panel-content" aria-labelledby="iv-tab-content"
                        data-iv-panel="content" hidden>
                        @foreach (['groom_name' => 'text', 'bride_name' => 'text', 'date' => 'date', 'time' => 'time', 'venue' => 'text'] as $field => $type)
                            <div class="iv-field"><label
                                    for="iv-{{ $field }}">{{ __('invitations.' . $field) }}</label><input
                                    id="iv-{{ $field }}" type="{{ $type }}" name="{{ $field }}"
                                    value="{{ old($field, $field === 'date' ? $selected?->date?->format('Y-m-d') : $selected?->$field) }}"
                                    @if ($type === 'text') maxlength="{{ $field === 'venue' ? 255 : 120 }}" @endif>
                            </div>
                        @endforeach
                        <div class="iv-field"><label for="iv-message">{{ __('invitations.message') }}</label>
                            <textarea id="iv-message" name="message" rows="5" maxlength="3000">{{ old('message', $selected?->message) }}</textarea>
                        </div>
                    </div>
                    <div class="iv-panel" role="tabpanel" id="iv-panel-design" aria-labelledby="iv-tab-design"
                        data-iv-panel="design" hidden>
                        <div class="iv-field"><label for="iv-template">{{ __('invitations.template') }}</label><select
                                name="template" id="iv-template">
                                @foreach ($templates as $key => $definition)
                                    <option value="{{ $key }}" @selected(old('template', $selected?->template ?? ($filters['template'] ?? 'floral')) === $key)>
                                        {{ __('invitations.' . ($key === 'photo' ? 'photo_template' : $key)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="iv-field"><label for="iv-accent">{{ __('invitations.accent') }}</label><input
                                type="color" name="accent" id="iv-accent"
                                value="{{ old('accent', $selected?->accent ?? '#D81B60') }}"></div>
                    </div>
                    <div class="iv-panel" role="tabpanel" id="iv-panel-seo" aria-labelledby="iv-tab-seo"
                        data-iv-panel="seo" hidden>
                        @foreach (['seo_title' => 160, 'seo_description' => 320] as $field => $limit)
                            <div class="iv-field"><label
                                    for="iv-{{ $field }}">{{ __('invitations.' . $field) }}</label><input
                                    name="{{ $field }}" id="iv-{{ $field }}"
                                    maxlength="{{ $limit }}" value="{{ old($field, $selected?->$field) }}"></div>
                        @endforeach
                    </div>
                    <div class="iv-editor-actions"><button type="button" class="iv-button"
                            data-iv-form-preview><x-admin::icon
                                name="show" />{{ __('invitations.preview') }}</button><button class="iv-button"
                            type="reset">{{ __('invitations.cancel') }}</button><button class="iv-button is-primary"
                            type="submit"><x-admin::icon name="save" />{{ __('invitations.save') }}</button></div>
                </form>
            </section>
        </div>
        <dialog class="iv-dialog" data-iv-delete-dialog aria-labelledby="iv-delete-title">
            <form method="POST" data-iv-delete-form>@csrf @method('DELETE')<h2 id="iv-delete-title">
                    {{ __('invitations.delete') }}</h2>
                <p>{{ __('invitations.confirm') }}</p><strong data-iv-delete-name></strong>
                <div class="iv-editor-actions"><button type="button" class="iv-button"
                        data-iv-close-delete>{{ __('invitations.close') }}</button><button class="iv-button is-primary"
                        type="submit">{{ __('invitations.delete') }}</button></div>
            </form>
        </dialog>
        <dialog class="iv-dialog iv-preview-dialog" data-iv-preview-dialog aria-label="{{ __('invitations.preview') }}">
            <button type="button" class="iv-button" data-iv-close-preview>{{ __('invitations.close') }}</button>
            <p data-iv-loading role="status" hidden>{{ __('invitations.loading') }}</p>
            <div data-iv-preview-content data-title="{{ __('invitations.preview') }}"></div>
        </dialog>
    </div>
@endsection
