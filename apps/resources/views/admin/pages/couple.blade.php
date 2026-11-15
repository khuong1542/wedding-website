@extends('admin.layouts.index')

@push('styles')
    @vite('resources/css/admin/pages/couple.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/pages/couple.js')
@endpush

@section('description', __('couple.description'))

@section('actions')
    <div class="couple-actions couple-heading-actions">
        <button type="reset" form="couple-form"
            class="couple-button couple-button-secondary">{{ __('couple.reset') }}</button>
        <button type="submit" form="couple-form" class="couple-button"><x-admin::icon
                name="save" />{{ __('couple.save') }}</button>
    </div>
@endsection

@section('content')
    <form id="couple-form" method="POST" action="{{ route('admin.couple.update', ['lang' => app()->getLocale()]) }}"
        enctype="multipart/form-data" class="couple-form" data-couple-form data-saved="{{ __('couple.saved') }}"
        data-unsaved="{{ __('couple.unsaved') }}" data-save-error="{{ __('couple.save_error') }}"
        data-photo-error="{{ __('couple.photo_error') }}" data-photo-read-error="{{ __('couple.photo_read_error') }}">
        @csrf
        @method('PUT')
        @foreach (['groom', 'bride'] as $person)
            <input type="hidden" name="remove_{{ $person }}_photo"
                value="{{ old('remove_' . $person . '_photo', '0') }}" data-photo-removal="{{ $person }}">
        @endforeach
        @foreach (['groom', 'bride'] as $person)
            <input type="hidden" name="remove_{{ $person }}_bank_qr"
                value="{{ old('remove_' . $person . '_bank_qr', '0') }}" data-photo-removal="{{ $person }}-bank-qr">
        @endforeach
        <div class="couple-layout">
            <div class="couple-editor">
                @foreach (['groom', 'bride'] as $person)
                    <section class="admin-panel couple-person couple-person-{{ $person }}"
                        aria-labelledby="{{ $person }}-heading">
                        <div class="admin-panel-heading couple-section-heading"><span
                                class="couple-section-icon"><x-admin::icon name="user" /></span>
                            <div>
                                <h2 id="{{ $person }}-heading">{{ __('couple.' . $person) }}</h2>
                            </div>
                        </div>
                        <div class="couple-person-summary">
                            <div class="couple-photo-row">
                                <div class="couple-photo-picker">
                                    <label class="couple-portrait" for="{{ $person }}-photo">
                                        <span class="couple-photo-hint" data-photo-hint="{{ $person }}"
                                            @if ($couple?->{$person . '_photo'} && !old('remove_' . $person . '_photo')) hidden @endif>
                                            <x-admin::icon name="camera-plus" />
                                            <span>{{ __('couple.photo_help') }}</span>
                                        </span>
                                        <img data-photo-preview="{{ $person }}"
                                            alt="{{ __('couple.photo') }} — {{ __('couple.' . $person) }}"
                                            @if ($couple?->{$person . '_photo'} && !old('remove_' . $person . '_photo')) src="{{ route('admin.couple.photo', ['person' => $person]) }}" @else hidden @endif>
                                        <input class="couple-photo-input" id="{{ $person }}-photo"
                                            name="{{ $person }}_photo" type="file"
                                            accept="image/jpeg,image/png,image/webp" data-photo-input="{{ $person }}"
                                            aria-label="{{ __('couple.choose_photo') }} — {{ __('couple.' . $person) }}"
                                            aria-describedby="{{ $person }}-photo-help">
                                        <span class="sr-only"
                                            id="{{ $person }}-photo-help">{{ __('couple.photo_help') }}</span>
                                    </label>
                                    <button class="couple-photo-clear" type="button"
                                        data-photo-remove="{{ $person }}"
                                        aria-label="{{ __('couple.remove_photo') }} — {{ __('couple.' . $person) }}"
                                        title="{{ __('couple.remove_photo') }}"
                                        @if (!$couple?->{$person . '_photo'} || old('remove_' . $person . '_photo')) hidden @endif><x-admin::icon
                                            name="x" /></button>
                                </div>
                            </div>
                            <div class="couple-primary-fields">
                                @foreach (['full_name' => 'text', 'nickname' => 'text', 'birthday' => 'date'] as $field => $type)
                                    <div class="couple-field"><label
                                            for="{{ $person }}-{{ $field }}">{{ __('couple.' . $field) }}
                                            @if ($field === 'full_name')
                                                <span aria-hidden="true">*</span>
                                            @endif
                                        </label>
                                        <input id="{{ $person }}-{{ $field }}"
                                            name="{{ $person }}_{{ $field }}"
                                            value="{{ old($person . '_' . $field, $couple?->details[$person . '_' . $field] ?? '') }}"
                                            type="{{ $type }}" maxlength="160" @required($field === 'full_name')
                                            @if ($field === 'birthday') max="{{ date('Y-m-d') }}" @endif
                                            @if ($field === 'full_name') placeholder="{{ __('couple.name_placeholder') }}" @elseif ($field === 'display_name') placeholder="{{ __('couple.display_placeholder') }}" @endif>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="couple-fields">
                            <div class="couple-field couple-field-wide">
                                <label for="{{ $person }}-address">{{ __('couple.address') }}</label>
                                <input id="{{ $person }}-address" name="{{ $person }}_address"
                                    value="{{ old($person . '_address', $couple?->details[$person . '_address'] ?? '') }}"
                                    maxlength="255" autocomplete="street-address">
                            </div>
                            <div class="couple-field couple-field-wide"><label
                                    for="{{ $person }}-bio">{{ __('couple.bio') }}</label>
                                <textarea id="{{ $person }}-bio" name="{{ $person }}_bio" rows="4" maxlength="2000"
                                    placeholder="{{ __('couple.bio_placeholder') }}">{{ old($person . '_bio', $couple?->details[$person . '_bio'] ?? '') }}</textarea>
                            </div>
                            <h3>{{ __('couple.contact') }}</h3>
                            @foreach (['phone' => 'tel', 'facebook' => 'url'] as $field => $type)
                                <div class="couple-field"><label
                                        for="{{ $person }}-{{ $field }}">{{ __('couple.' . $field) }}</label><input
                                        id="{{ $person }}-{{ $field }}"
                                        name="{{ $person }}_{{ $field }}"
                                        value="{{ old($person . '_' . $field, $couple?->details[$person . '_' . $field] ?? '') }}"
                                        type="{{ $type }}" maxlength="255"
                                        @if ($type === 'url') placeholder="https://" @endif>
                                </div>
                            @endforeach
                            <div class="couple-field couple-field-wide couple-qr-section">
                                <h3><x-admin::icon name="qr" />{{ __('couple.bank_qr') }}</h3>
                                <div class="couple-photo-picker couple-qr-picker">
                                    <label class="couple-portrait couple-qr-frame" for="{{ $person }}-bank-qr">
                                        <span class="couple-photo-hint" data-photo-hint="{{ $person }}-bank-qr"
                                            @if ($couple?->{$person . '_bank_qr'} && !old('remove_' . $person . '_bank_qr')) hidden @endif>
                                            <x-admin::icon name="qr" />
                                            <span>{{ __('couple.photo_help') }}</span>
                                        </span>
                                        <img data-photo-preview="{{ $person }}-bank-qr"
                                            alt="{{ __('couple.bank_qr') }} — {{ __('couple.' . $person) }}"
                                            @if ($couple?->{$person . '_bank_qr'} && !old('remove_' . $person . '_bank_qr')) src="{{ route('admin.couple.qr', ['person' => $person]) }}" @else hidden @endif>
                                        <input class="couple-photo-input" id="{{ $person }}-bank-qr"
                                            name="{{ $person }}_bank_qr" type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            data-photo-input="{{ $person }}-bank-qr"
                                            aria-label="{{ __('couple.bank_qr') }} — {{ __('couple.' . $person) }}"
                                            aria-describedby="{{ $person }}-bank-qr-help">
                                        <span class="sr-only"
                                            id="{{ $person }}-bank-qr-help">{{ __('couple.photo_help') }}</span>
                                    </label>
                                    <button class="couple-photo-clear" type="button"
                                        data-photo-remove="{{ $person }}-bank-qr"
                                        aria-label="{{ __('couple.remove_photo') }} — {{ __('couple.' . $person) }}"
                                        title="{{ __('couple.remove_photo') }}"
                                        @if (!$couple?->{$person . '_bank_qr'} || old('remove_' . $person . '_bank_qr')) hidden @endif><x-admin::icon
                                            name="x" /></button>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach

            </div>
        </div>
    </form>
@endsection
