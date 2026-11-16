@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/wishes.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/wishes.js')
@endpush
@section('description', __('wishes.description'))
@section('content')
    <div class="wish-page" data-wishes>
        <div class="wish-stats">
            @foreach (['total', ...$statuses] as $status)
                <article class="admin-panel wish-stat wish-{{ $status }}"><span class="wish-stat-icon"><x-admin::icon
                            :name="[
                                'total' => 'message-heart',
                                'published' => 'check-circle',
                                'pending' => 'hourglass',
                                'hidden' => 'x-circle',
                            ][$status]" /></span>
                    <div>
                        <strong>{{ $status === 'total' ? $total : $counts[$status] }}</strong><span>{{ __('wishes.' . $status) }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <nav class="wish-tabs" aria-label="{{ __('wishes.list') }}">
            @foreach (['list', 'settings'] as $tab)
                <a class="wish-button {{ ($filters['tab'] ?? 'list') === $tab ? 'is-active' : '' }}"
                    href="{{ route('admin.wishes.index', ['tab' => $tab, 'lang' => app()->getLocale()]) }}"
                    @if (($filters['tab'] ?? 'list') === $tab) aria-current="page" @endif><x-admin::icon
                        :name="$tab === 'list' ? 'message-heart' : 'cog'" />{{ __('wishes.' . $tab) }}{{ $tab === 'list' ? ' (' . $total . ')' : '' }}</a>
            @endforeach
        </nav>
        @if (($filters['tab'] ?? 'list') === 'settings')
            <section class="admin-panel wish-settings">
                <h2>{{ __('wishes.settings') }}</h2>
                <form method="POST" action="{{ route('admin.wishes.settings', ['lang' => app()->getLocale()]) }}">@csrf
                    @method('PUT')
                    @foreach (['is_visible' => 'visible', 'allow_submissions' => 'allow_submissions', 'require_approval' => 'require_approval'] as $field => $label)
                        <div class="wish-setting-field"><input type="hidden" name="{{ $field }}"
                                value="0"><label class="wish-switch-label"
                                for="wish-{{ $field }}">{{ __('wishes.' . $label) }}<input
                                    id="wish-{{ $field }}" class="wish-switch" type="checkbox"
                                    name="{{ $field }}" value="1" role="switch"
                                    @checked(old($field, $settings->$field))></label></div>
                    @endforeach
                    <div class="wish-setting-field"><label
                            for="wish-display-limit">{{ __('wishes.display_limit') }}</label><input id="wish-display-limit"
                            name="display_limit" type="number" min="1" max="100"
                            value="{{ old('display_limit', $settings->display_limit) }}" required></div>
                    <div class="wish-setting-field"><label for="wish-setting-sort">{{ __('wishes.sort') }}</label><select
                            id="wish-setting-sort" name="sort">
                            @foreach (['newest', 'oldest'] as $sort)
                                <option value="{{ $sort }}" @selected(old('sort', $settings->sort) === $sort)>
                                    {{ __('wishes.' . $sort) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="wish-detail-actions"><button class="wish-button is-active" type="submit"><x-admin::icon
                                name="save" />{{ __('wishes.save') }}</button></div>
                </form>
            </section>
        @else
            <div class="wish-workspace {{ $selected ? 'has-detail' : '' }}">
                <section class="admin-panel wish-list">
                    <form class="wish-filters" method="GET" action="{{ route('admin.wishes.index') }}"><input
                            type="hidden" name="lang" value="{{ app()->getLocale() }}"><input type="hidden"
                            name="per_page" value="{{ $wishes->perPage() }}"><label class="wish-search"><span
                                class="sr-only">{{ __('wishes.search') }}</span><input name="q"
                                value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('wishes.search') }}"
                                maxlength="120"></label><label><span
                                class="sr-only">{{ __('wishes.status') }}</span><select name="status">
                                <option value="">{{ __('wishes.all_statuses') }}</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ __('wishes.' . $status) }}</option>
                                @endforeach
                            </select>
                        </label><label><span class="sr-only">{{ __('wishes.sort') }}</span><select name="sort">
                                @foreach (['newest', 'oldest'] as $sort)
                                    <option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>
                                        {{ __('wishes.' . $sort) }}</option>
                                @endforeach
                            </select>
                        </label><button class="wish-button is-active" type="submit"><x-admin::icon
                                name="filter" />{{ __('wishes.filter') }}</button></form>
                    <form id="wish-bulk-form" method="POST"
                        action="{{ route('admin.wishes.bulk', ['lang' => app()->getLocale()]) }}">@csrf
                        <div class="wish-table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col"><input type="checkbox" data-wish-all
                                                aria-label="{{ __('wishes.select_all') }}"></th>
                                        @foreach (['sender', 'message', 'date', 'status', 'actions'] as $column)
                                            <th scope="col">{{ __('wishes.' . $column) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wishes as $wish)
                                        <tr class="{{ $selected?->id === $wish->id ? 'is-selected' : '' }}">
                                            <td><input type="checkbox" name="ids[]" value="{{ $wish->id }}"
                                                    data-wish-check
                                                    aria-label="{{ __('wishes.view') }} — {{ $wish->name }}"></td>
                                            <td>
                                                <div class="wish-guest"><span
                                                        class="wish-avatar">{{ mb_substr($wish->name, 0, 1) }}</span><strong>{{ $wish->name }}</strong>
                                                </div>
                                            </td>
                                            <td><span class="wish-message-excerpt">{{ $wish->message }}</span></td>
                                            <td>{{ $wish->created_at->format('d/m/Y') }}<small>{{ $wish->created_at->format('H:i') }}</small>
                                            </td>
                                            <td><span
                                                    class="wish-badge wish-{{ $wish->status }}">{{ __('wishes.' . $wish->status) }}</span>
                                            </td>
                                            <td>
                                                <div class="wish-row-actions"><a
                                                        href="{{ route('admin.wishes.index', array_merge(request()->except(['new', 'edit']), ['selected' => $wish->id])) }}"
                                                        aria-label="{{ __('wishes.view') }} — {{ $wish->name }}"><x-admin::icon
                                                            name="show" /></a></div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="wish-empty" colspan="6"><x-admin::icon name="message-heart" />
                                                <p>{{ __('wishes.empty') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="wish-bulk-actions"><label><input type="checkbox"
                                    data-wish-all>{{ __('wishes.select_all') }}</label><span data-wish-count
                                aria-live="polite"></span><button type="submit" class="wish-button" name="action"
                                value="publish" data-wish-bulk disabled><x-admin::icon
                                    name="check" />{{ __('wishes.publish') }}</button><button type="submit"
                                class="wish-button" name="action" value="hide" data-wish-bulk disabled><x-admin::icon
                                    name="hide" />{{ __('wishes.hide') }}</button></div>
                    </form>
                    <div class="wish-pagination">
                        <span>{{ __('wishes.showing', ['from' => $wishes->firstItem() ?? 0, 'to' => $wishes->lastItem() ?? 0, 'total' => $wishes->total()]) }}</span>
                        <form method="GET" action="{{ route('admin.wishes.index') }}">
                            @foreach (request()->except(['page', 'per_page', 'selected', 'new', 'edit']) as $key => $value)
                                @if (is_scalar($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label>
                                <span class="sr-only">{{ __('wishes.per_page', ['count' => '']) }}</span><select
                                    name="per_page" data-wish-per-page>
                                    @foreach ([10, 25, 50] as $size)
                                        <option value="{{ $size }}" @selected($wishes->perPage() === $size)>
                                            {{ __('wishes.per_page', ['count' => $size]) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </form>
                        <nav aria-label="{{ __('wishes.list') }}">
                            @if ($wishes->previousPageUrl())
                                <a class="wish-button" href="{{ $wishes->previousPageUrl() }}"
                                    aria-label="{{ __('wishes.previous') }}">‹</a>
                            @endif
                            <span>
                                {{ $wishes->currentPage() }} / {{ $wishes->lastPage() }}</span>
                            @if ($wishes->nextPageUrl())
                                <a class="wish-button" href="{{ $wishes->nextPageUrl() }}"
                                    aria-label="{{ __('wishes.next') }}">›</a>
                            @endif
                        </nav>
                    </div>
                </section>
                @if ($selected)
                    <aside class="admin-panel wish-detail">
                        <header>
                            <h2>{{ __('wishes.details') }}</h2><a
                                href="{{ route('admin.wishes.index', array_merge(request()->except(['selected', 'new', 'edit']), ['selected' => 0])) }}"
                                aria-label="{{ __('wishes.close') }}" data-wish-close-detail><x-admin::icon
                                    name="x" /></a>
                        </header>
                        <div class="wish-detail-body">
                            <div class="wish-guest"><span
                                    class="wish-avatar">{{ mb_substr($selected->name, 0, 1) }}</span>
                                <div>
                                    <h3>{{ $selected->name }}</h3>
                                    <small>{{ $selected->email ?: '—' }}</small><small>{{ $selected->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                            <blockquote>{{ $selected->message }}</blockquote>
                            <form method="POST"
                                action="{{ route('admin.wishes.status', ['wish' => $selected->id, 'lang' => app()->getLocale()]) }}">
                                @csrf @method('PATCH')<div class="wish-field"><label
                                        for="wish-status">{{ __('wishes.status') }}</label><select id="wish-status"
                                        name="status" data-wish-status>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($selected->status === $status)>
                                                {{ __('wishes.' . $status) }}</option>
                                        @endforeach
                                    </select></div><label class="wish-switch-label"
                                    for="wish-visible">{{ __('wishes.visible') }}<input class="wish-switch"
                                        id="wish-visible" type="checkbox" role="switch" data-wish-visible
                                        @checked($selected->status === 'published')></label><button type="submit" class="wish-button"
                                    data-wish-status-save>{{ __('wishes.save') }}</button></form>

                        </div>
                    </aside>
                @endif
            </div>
        @endif

    </div>
@endsection
