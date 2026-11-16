@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/rsvp.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/rsvp.js')
@endpush
@section('description', __('rsvp.description'))
@section('content')
    <div class="rsvp-page">
        <nav class="rsvp-tabs" aria-label="{{ __('rsvp.status') }}">
            @foreach (['all', ...$statuses] as $status)
                <a class="rsvp-button {{ ($filters['status'] ?? 'all') === $status ? 'is-active' : '' }}"
                    href="{{ route('admin.rsvp.index', array_merge(request()->except(['status', 'page', 'selected', 'edit']), $status === 'all' ? [] : ['status' => $status])) }}"
                    @if (($filters['status'] ?? 'all') === $status) aria-current="page" @endif>{{ __('rsvp.' . $status) }}
                    ({{ $status === 'all' ? $total : $counts[$status] }})
                </a>
            @endforeach
        </nav>
        <div class="rsvp-stats">
            @foreach (['total', ...$statuses] as $status)
                <article class="admin-panel rsvp-stat rsvp-{{ $status }}">
                    <span class="rsvp-stat-icon"><x-admin::icon :name="[
                        'total' => 'group',
                        'attending' => 'check-circle',
                        'declined' => 'x-circle',
                        'pending' => 'time',
                    ][$status]" /></span>
                    <div>
                        <strong>{{ $status === 'total' ? $total : $counts[$status] }}</strong><span>{{ __('rsvp.' . $status) }}</span>
                    </div>
                    @if ($status !== 'total')
                        <small>{{ $total ? number_format(($counts[$status] / $total) * 100, 1) : '0' }}%</small>
                    @endif
                </article>
            @endforeach
        </div>
        <form class="admin-panel rsvp-filters" method="GET" action="{{ route('admin.rsvp.index') }}">
            <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
            <input type="hidden" name="per_page" value="{{ $responses->perPage() }}">
            <label class="rsvp-search"><span class="sr-only">{{ __('rsvp.search') }}</span><input name="q"
                    value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('rsvp.search') }}" maxlength="120"></label>
            <label><span class="sr-only">{{ __('rsvp.group') }}</span><select name="group">
                    <option value="">{{ __('rsvp.all_groups') }}</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group }}" @selected(($filters['group'] ?? '') === $group)>{{ __('rsvp.' . $group) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label><span class="sr-only">{{ __('rsvp.status') }}</span><select name="status">
                    <option value="">{{ __('rsvp.all_statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('rsvp.' . $status) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label><span class="sr-only">{{ __('rsvp.date') }}</span><input type="date" name="date"
                    aria-label="{{ __('rsvp.date') }}" value="{{ $filters['date'] ?? '' }}"></label>
            <button class="rsvp-button is-active" type="submit"><x-admin::icon
                    name="filter" />{{ __('rsvp.filter') }}</button>
            <a class="rsvp-button"
                href="{{ route('admin.rsvp.index', ['lang' => app()->getLocale()]) }}">{{ __('rsvp.reset') }}</a>
        </form>
        <div class="rsvp-workspace {{ $selected ? 'has-detail' : '' }}">
            <section class="admin-panel rsvp-list">
                <div class="rsvp-table-scroll">
                    <table>
                        <thead>
                            <tr>
                                @foreach (['guest', 'group', 'attendees', 'status', 'date', 'notes', 'actions'] as $column)
                                    <th scope="col">{{ __('rsvp.' . $column) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($responses as $response)
                                <tr class="{{ $selected?->id === $response->id ? 'is-selected' : '' }}">
                                    <td>
                                        <div class="rsvp-guest"><span
                                                class="rsvp-avatar">{{ mb_substr($response->name, 0, 1) }}</span>
                                            <div>
                                                <strong>{{ $response->name }}</strong><small>{{ $response->email ?: $response->phone ?: '—' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="rsvp-group">{{ __('rsvp.' . $response->group) }}</span></td>
                                    <td>{{ $response->attendees }}</td>
                                    <td><span
                                            class="rsvp-badge rsvp-{{ $response->status }}">{{ __('rsvp.' . $response->status) }}</span>
                                    </td>
                                    <td>{{ $response->responded_at?->format('d/m/Y') ?? '—' }}<small>{{ $response->responded_at?->format('H:i') }}</small>
                                    </td>
                                    <td><span class="rsvp-note"
                                            title="{{ $response->notes }}">{{ $response->notes ?: '—' }}</span></td>
                                    <td>
                                        <div class="rsvp-row-actions">
                                            <a href="{{ route('admin.rsvp.index', array_merge(request()->except('edit'), ['selected' => $response->id])) }}"
                                                aria-label="{{ __('rsvp.view') }} — {{ $response->name }}"><x-admin::icon
                                                    name="show" /></a>
                                            <a href="{{ route('admin.rsvp.index', array_merge(request()->query(), ['selected' => $response->id, 'edit' => 1])) }}"
                                                aria-label="{{ __('rsvp.edit') }} — {{ $response->name }}"><x-admin::icon
                                                    name="edit" /></a>
                                            <button type="button"
                                                data-rsvp-delete="{{ route('admin.rsvp.destroy', ['response' => $response->id, 'lang' => app()->getLocale()]) }}"
                                                data-name="{{ $response->name }}"
                                                aria-label="{{ __('rsvp.delete') }} — {{ $response->name }}"><x-admin::icon
                                                    name="trash" /></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="rsvp-empty" colspan="7"><x-admin::icon name="envelope" />
                                        <p>{{ __('rsvp.empty') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="rsvp-pagination">
                    <span>{{ __('rsvp.showing', ['from' => $responses->firstItem() ?? 0, 'to' => $responses->lastItem() ?? 0, 'total' => $responses->total()]) }}</span>
                    <form method="GET" action="{{ route('admin.rsvp.index') }}">
                        @foreach (request()->except(['page', 'per_page', 'selected', 'edit']) as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label>
                            <span class="sr-only">{{ __('rsvp.per_page', ['count' => '']) }}</span><select name="per_page"
                                data-rsvp-per-page>
                                @foreach ([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected($responses->perPage() === $size)>
                                        {{ __('rsvp.per_page', ['count' => $size]) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </form>
                    <nav aria-label="{{ __('rsvp.all') }}">
                        @if ($responses->previousPageUrl())
                            <a class="rsvp-button" href="{{ $responses->previousPageUrl() }}"
                                aria-label="{{ __('rsvp.previous') }}">‹</a>
                        @endif
                        <span>
                            {{ $responses->currentPage() }} / {{ $responses->lastPage() }}</span>
                        @if ($responses->nextPageUrl())
                            <a class="rsvp-button" href="{{ $responses->nextPageUrl() }}"
                                aria-label="{{ __('rsvp.next') }}">›</a>
                        @endif
                    </nav>
                </div>
            </section>
            @if ($selected)
                <aside class="admin-panel rsvp-detail">
                    <header>
                        <h2>{{ __('rsvp.' . (!empty($filters['edit']) ? 'edit' : 'details')) }}</h2><a
                            href="{{ route('admin.rsvp.index', request()->except(['selected', 'edit'])) }}"
                            aria-label="{{ __('rsvp.close') }}"><x-admin::icon name="x" /></a>
                    </header>
                    @if (!empty($filters['edit']))
                        <form method="POST"
                            action="{{ route('admin.rsvp.update', ['response' => $selected->id, 'lang' => app()->getLocale()]) }}"
                            class="rsvp-edit">
                            @csrf @method('PUT')
                            @foreach (['name', 'email', 'phone'] as $field)
                                <label
                                    for="rsvp-{{ $field }}">{{ __('rsvp.' . $field) }}{{ $field === 'name' ? ' *' : '' }}</label><input
                                    id="rsvp-{{ $field }}" name="{{ $field }}"
                                    type="{{ $field === 'email' ? 'email' : 'text' }}"
                                    value="{{ old($field, $selected->$field) }}"
                                    maxlength="{{ ['name' => 120, 'email' => 255, 'phone' => 30][$field] }}"
                                    @required($field === 'name')>
                            @endforeach
                            @foreach (['group' => $groups, 'status' => $statuses] as $field => $options)
                                <label for="rsvp-{{ $field }}">{{ __('rsvp.' . $field) }} *</label><select
                                    id="rsvp-{{ $field }}" name="{{ $field }}" required>
                                    @foreach ($options as $option)
                                        <option value="{{ $option }}" @selected(old($field, $selected->$field) === $option)>
                                            {{ __('rsvp.' . $option) }}</option>
                                    @endforeach
                                </select>
                            @endforeach
                            <label for="rsvp-attendees">{{ __('rsvp.attendees') }} *</label><input id="rsvp-attendees"
                                name="attendees" type="number" min="0" max="100" required
                                value="{{ old('attendees', $selected->attendees) }}">
                            <label for="rsvp-notes">{{ __('rsvp.notes') }}</label>
                            <textarea id="rsvp-notes" name="notes" rows="4" maxlength="3000">{{ old('notes', $selected->notes) }}</textarea>
                            <div class="rsvp-detail-actions"><a class="rsvp-button"
                                    href="{{ route('admin.rsvp.index', ['selected' => $selected->id, 'lang' => app()->getLocale()]) }}">{{ __('rsvp.cancel') }}</a><button
                                    class="rsvp-button is-active" type="submit">{{ __('rsvp.save') }}</button></div>
                        </form>
                    @else
                        <div class="rsvp-detail-body">
                            <div class="rsvp-guest"><span
                                    class="rsvp-avatar">{{ mb_substr($selected->name, 0, 1) }}</span>
                                <div>
                                    <h3>{{ $selected->name }}</h3>
                                    <small>{{ $selected->email ?: '—' }}</small><small>{{ $selected->phone ?: '—' }}</small>
                                </div>
                            </div>
                            <p class="rsvp-badge rsvp-{{ $selected->status }}">{{ __('rsvp.' . $selected->status) }}
                            </p>
                            <dl>
                                @foreach (['group' => __('rsvp.' . $selected->group), 'date' => $selected->responded_at?->format('d/m/Y H:i') ?? '—', 'attendees' => $selected->attendees, 'notes' => $selected->notes ?: '—'] as $label => $value)
                                    <div>
                                        <dt>{{ __('rsvp.' . $label) }}</dt>
                                        <dd>{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            <div class="rsvp-detail-actions"><a class="rsvp-button"
                                    href="{{ route('admin.rsvp.index', array_merge(request()->query(), ['edit' => 1])) }}"><x-admin::icon
                                        name="edit" />{{ __('rsvp.edit') }}</a><button type="button"
                                    class="rsvp-button"
                                    data-rsvp-delete="{{ route('admin.rsvp.destroy', ['response' => $selected->id, 'lang' => app()->getLocale()]) }}"
                                    data-name="{{ $selected->name }}"><x-admin::icon
                                        name="trash" />{{ __('rsvp.delete') }}</button></div>
                        </div>
                    @endif
                </aside>
            @endif
        </div>
        <dialog class="rsvp-dialog" data-rsvp-confirm aria-labelledby="rsvp-confirm-title">
            <form method="POST" data-rsvp-delete-form>@csrf @method('DELETE')<h2 id="rsvp-confirm-title">
                    {{ __('rsvp.delete') }}</h2>
                <p>{{ __('rsvp.confirm') }}</p><strong data-rsvp-delete-name></strong>
                <div class="rsvp-detail-actions"><button type="button" class="rsvp-button"
                        data-rsvp-close>{{ __('rsvp.close') }}</button><button class="rsvp-button is-active"
                        type="submit">{{ __('rsvp.delete') }}</button></div>
            </form>
        </dialog>
    </div>
@endsection
