@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/events.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/events.js')
@endpush
@section('description', $isShared ? __('events.shared_description') : __('events.description'))
@section('actions')
    <a class="event-button" href="{{ route($eventIndexRoute, ['side' => $side, 'new' => 1]) }}">
        <x-admin::icon name="plus" />{{ __('events.add') }}</a>
@endsection
@section('content')
    <div class="event-page" data-events data-status-saved="{{ __('events.status_saved') }}"
        data-status-error="{{ __('events.status_error') }}" data-photo-error="{{ __('events.photo_error') }}">
        @unless ($isShared)
            <nav class="event-side-tabs" aria-label="{{ __('admin.wedding_events') }}">
                @foreach (['groom', 'bride'] as $person)
                    <a href="{{ route($eventIndexRoute, ['side' => $person]) }}"
                        @if ($side === $person) aria-current="page" @endif><x-admin::icon
                            name="user" />{{ __('events.' . $person) }}</a>
                @endforeach
            </nav>
        @endunless
        <div class="event-workspace">
            <section class="admin-panel event-list-panel">
                <div class="event-panel-heading">
                    <h2><x-admin::icon
                            name="user" />{{ $isShared ? __('events.list') : __('events.' . $side . '_events') }}</h2>
                    <span class="event-count" data-event-count>{{ $events->count() }}</span>
                </div>
                <div class="event-filters"><label class="sr-only"
                        for="event-search">{{ __('events.search') }}</label><input id="event-search" type="search"
                        placeholder="{{ __('events.search') }}" data-event-search><label class="sr-only"
                        for="event-status">{{ __('events.all') }}</label><select id="event-status" data-event-filter>
                        <option value="all">{{ __('events.all') }}</option>
                        <option value="1">{{ __('events.visible') }}</option>
                        <option value="0">{{ __('events.hidden') }}</option>
                    </select></div>
                <div class="event-cards">
                    @foreach ($events as $event)
                        <article class="event-card {{ $selectedEvent?->id === $event->id ? 'is-active' : '' }}"
                            data-event-card data-id="{{ $event->id }}" data-title="{{ $event->title }}"
                            data-venue="{{ $event->venue }}" data-visible="{{ (int) $event->is_visible }}">
                            <a class="event-card-main"
                                href="{{ route($eventIndexRoute, ['side' => $side, 'event' => $event->id]) }}"
                                @if ($selectedEvent?->id === $event->id) aria-current="true" @endif>
                                @if ($event->photo)
                                <img src="{{ route('admin.events.photo', $event) }}" alt="">@else<span
                                        class="event-card-placeholder"><x-admin::icon name="calendar-event" /></span>
                                @endif
                                <span class="event-card-text"><strong
                                        title="{{ $event->title }}">{{ $event->title }}</strong><small><x-admin::icon
                                            name="calendar" />{{ \Illuminate\Support\Carbon::parse($event->date)->format('d/m/Y') }}</small><small><x-admin::icon
                                            name="map" />{{ $event->venue }}</small></span>
                            </a>
                            <button class="event-card-toggle" type="button" role="switch"
                                aria-checked="{{ $event->is_visible ? 'true' : 'false' }}"
                                aria-label="{{ __('events.show') }} — {{ $event->title }}"
                                data-status-url="{{ route('admin.events.visibility', $event) }}"></button>
                            <details class="event-options">
                                <summary aria-label="{{ __('events.options') }}"><x-admin::icon
                                        name="dots-vertical-rounded" /></summary>
                                <div><a
                                        href="{{ route($eventIndexRoute, ['side' => $side, 'event' => $event->id]) }}">{{ __('events.edit') }}</a><button
                                        type="button"
                                        data-delete-event="{{ route('admin.events.destroy', ['event' => $event, 'lang' => app()->getLocale()]) }}"
                                        data-name="{{ $event->title }}">{{ __('events.delete') }}</button></div>
                            </details>
                        </article>
                    @endforeach
                </div>
                <p class="event-empty" data-event-empty @if ($events->isNotEmpty()) hidden @endif>
                    {{ __('events.empty') }}</p>
            </section>
            <section class="admin-panel event-editor">
                <div class="event-panel-heading">
                    <h2><x-admin::icon name="edit" />{{ $selectedEvent ? __('events.edit') : __('events.new') }}
                        @unless ($isShared)
                            ({{ __('events.' . $side) }})
                        @endunless
                    </h2>
                </div>
                <form method="POST" action="{{ route($eventSaveRoute, ['lang' => app()->getLocale()]) }}"
                    enctype="multipart/form-data" data-event-form>
                    @csrf
                    <input type="hidden" name="id" value="{{ old('id', $selectedEvent?->id) }}"><input type="hidden"
                        name="side" value="{{ old('side', $side) }}">
                    <div class="event-editor-body">
                        <div class="event-photo-picker"><label class="event-photo-frame" for="event-photo"><span
                                    data-photo-hint><x-admin::icon
                                        name="camera-plus" /><span>{{ __('events.photo_help') }}</span></span><img
                                    data-event-image alt="{{ __('events.photo') }}"
                                    @if ($selectedEvent?->photo && !old('remove_photo')) src="{{ route('admin.events.photo', $selectedEvent) }}" @else hidden @endif><input
                                    type="file" name="photo" id="event-photo" data-event-photo
                                    accept="image/jpeg,image/png,image/webp"
                                    aria-label="{{ __('events.photo') }}"></label><button type="button"
                                class="event-remove-photo" data-remove-photo
                                aria-label="{{ __('events.remove_photo') }}"><x-admin::icon
                                    name="x" /></button><input type="hidden" name="remove_photo"
                                value="{{ old('remove_photo', '0') }}" data-remove-photo-value></div>
                        <div class="event-primary-fields">
                            <div class="event-field"><label for="event-title">{{ __('events.title') }} *</label><input
                                    id="event-title" name="title"
                                    value="{{ old('title', $selectedEvent?->title ?? '') }}" maxlength="120" required>
                            </div>
                            <div class="event-field"><label for="event-type">{{ __('events.type') }} *</label><select
                                    id="event-type" name="type" required>
                                    @foreach (\App\Models\WeddingEvent::TYPES as $type)
                                        <option value="{{ $type }}" @selected(old('type', $selectedEvent?->type ?? 'ceremony') === $type)>
                                            {{ __('events.' . $type) }}</option>
                                    @endforeach
                                </select>
                            </div><label class="event-switch-label"><input type="hidden" name="is_visible"
                                    value="0"><input type="checkbox" name="is_visible" value="1"
                                    @checked(old('is_visible', $selectedEvent?->is_visible ?? true))><span
                                    class="event-switch"></span>{{ __('events.show') }}</label>
                        </div>
                        @foreach (['date' => 'date', 'start_time' => 'time', 'end_time' => 'time', 'venue' => 'text', 'map_url' => 'url'] as $field => $type)
                            <div
                                class="event-field {{ in_array($field, ['venue', 'map_url']) ? 'event-field-wide' : '' }}">
                                <label for="event-{{ $field }}">{{ __('events.' . $field) }} @if (in_array($field, ['date', 'start_time', 'venue']))
                                        *
                                    @endif
                                </label><input id="event-{{ $field }}" name="{{ $field }}"
                                    type="{{ $type }}"
                                    value="{{ old($field, in_array($field, ['start_time', 'end_time']) ? substr($selectedEvent?->$field ?? '', 0, 5) : $selectedEvent?->$field ?? '') }}"
                                    @required(in_array($field, ['date', 'start_time', 'venue']))
                                    @if ($type === 'text') maxlength="255" @elseif ($type === 'url') maxlength="1000" placeholder="https://maps.google.com/..." @endif>
                            </div>
                        @endforeach
                        <div class="event-field event-field-wide"><label
                                for="event-description">{{ __('events.description_field') }}</label>
                            <textarea id="event-description" name="description" rows="7" maxlength="3000">{{ old('description', $selectedEvent?->description ?? '') }}</textarea><span class="event-character-count" data-event-count-text></span>
                        </div>
                    </div>
                    <div class="event-actions"><button type="reset"
                            class="event-button event-button-secondary">{{ __('events.cancel') }}</button><button
                            type="submit" class="event-button"><x-admin::icon
                                name="save" />{{ __('events.save') }}</button></div>
                </form>
            </section>
        </div>
        <dialog class="event-confirm" data-event-dialog aria-labelledby="event-confirm-title">
            <form method="POST" data-delete-form>@csrf @method('DELETE')<h2 id="event-confirm-title">
                    {{ __('events.delete') }}</h2>
                <p>{{ __('events.confirm') }}</p><strong data-delete-name></strong>
                <div class="event-actions"><button type="button" class="event-button event-button-secondary"
                        data-cancel-delete>{{ __('events.close') }}</button><button
                        class="event-button event-button-danger" type="submit">{{ __('events.delete') }}</button></div>
            </form>
        </dialog>
    </div>
@endsection
