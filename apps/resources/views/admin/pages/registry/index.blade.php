@extends('admin.layouts.index')

@push('styles')
    @vite('resources/css/admin/pages/registry.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/registry.js')
@endpush
@section('description', __('registry.description'))
@section('content')
    <form class="registry-form" id="registry-form" method="POST"
        action="{{ route('admin.registry.update', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data"
        data-registry data-copied="{{ __('registry.copied') }}" data-copy-error="{{ __('registry.copy_error') }}"
        data-image-error="{{ __('registry.image_error') }}" data-limit="{{ __('registry.limit') }}">
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="registry-errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <section id="registry-panel-accounts">
            <div class="admin-panel">
                <div class="registry-panel-heading">
                    <h2><x-admin::icon name="credit-card" />{{ __('registry.accounts') }}</h2><label
                        class="registry-switch-label">{{ __('registry.visible') }}<input type="hidden" name="enabled"
                            value="0"><input type="checkbox" name="enabled" value="1"
                            @checked(old('enabled', $registry?->settings['enabled'] ?? false))><span class="registry-switch"></span></label>
                </div>
                <div class="registry-accounts">
                    @foreach (['bride', 'groom'] as $person)
                        <article class="registry-account registry-account-{{ $person }}">
                            <div class="registry-account-heading">
                                <h3><x-admin::icon name="user" />{{ __('registry.' . $person) }}</h3><label
                                    class="registry-switch-label"><span class="sr-only">{{ __('registry.visible') }} —
                                        {{ __('registry.' . $person) }}</span><input type="hidden"
                                        name="{{ $person }}_enabled" value="0"><input type="checkbox"
                                        name="{{ $person }}_enabled" value="1"
                                        @checked(old($person . '_enabled', $registry?->settings[$person . '_enabled'] ?? false))><span class="registry-switch"></span></label>
                            </div>
                            <div class="registry-account-body">
                                <div class="registry-bank-fields">
                                    @foreach (['bank', 'account', 'holder', 'memo'] as $field)
                                        <div class="registry-field"><label
                                                for="{{ $person }}-{{ $field }}">{{ __('registry.' . $field) }}</label>
                                            <div class="registry-input-wrap"><input
                                                    id="{{ $person }}-{{ $field }}"
                                                    name="{{ $person }}_{{ $field }}"
                                                    value="{{ old($person . '_' . $field, $registry?->settings[$person . '_' . $field] ?? '') }}"
                                                    maxlength="{{ $field === 'memo' ? 255 : 160 }}"
                                                    @if ($field === 'bank') list="registry-banks" @endif
                                                    @if ($field === 'account') data-account="{{ $person }}" @endif>
                                                @if ($field === 'account')
                                                    <button type="button" class="registry-copy"
                                                        data-copy-account="{{ $person }}"
                                                        aria-label="{{ __('registry.copy') }}"><x-admin::icon
                                                            name="copy" /></button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="registry-qr-column">
                                    <div class="registry-qr-picker">
                                        <label class="registry-qr-frame" for="{{ $person }}-registry-qr"><span
                                                data-qr-hint="{{ $person }}"><x-admin::icon
                                                    name="qr" /><span>{{ __('registry.photo_help') }}</span></span><img
                                                data-qr-image="{{ $person }}"
                                                alt="{{ __('registry.qr') }} — {{ __('registry.' . $person) }}"
                                                @if ($couple?->{$person . '_bank_qr'} && !old('remove_' . $person . '_bank_qr')) src="{{ route('admin.couple.qr', $person) }}" @else hidden @endif><input
                                                type="file" id="{{ $person }}-registry-qr"
                                                name="{{ $person }}_bank_qr" data-qr-input="{{ $person }}"
                                                accept="image/jpeg,image/png,image/webp"
                                                aria-label="{{ __('registry.upload') }} — {{ __('registry.' . $person) }}"></label>
                                        <input type="hidden" name="remove_{{ $person }}_bank_qr"
                                            value="{{ old('remove_' . $person . '_bank_qr', '0') }}"
                                            data-qr-removal="{{ $person }}"><button type="button"
                                            class="registry-qr-remove" data-remove-qr="{{ $person }}"
                                            aria-label="{{ __('registry.remove_qr') }}"><x-admin::icon
                                                name="x" /></button>
                                    </div><a class="registry-download" data-qr-download="{{ $person }}"
                                        href="{{ route('admin.couple.qr', $person) }}"
                                        download="{{ $person }}-qr"><x-admin::icon
                                            name="arrow-down-stroke-square" />{{ __('registry.download') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <section class="admin-panel registry-message">
                <div class="registry-panel-heading">
                    <h2><x-admin::icon name="edit" />{{ __('registry.message') }}</h2>
                </div>
                <div class="registry-content"><label class="sr-only"
                        for="registry-message">{{ __('registry.message') }}</label>
                    <textarea id="registry-message" name="message" rows="5" maxlength="3000">{{ old('message', $registry?->settings['message'] ?? '') }}</textarea>
                </div>
            </section>
        </section>
        <datalist id="registry-banks">
            @foreach (['Vietcombank', 'Techcombank', 'BIDV', 'VietinBank', 'Agribank', 'MB Bank', 'ACB', 'VPBank', 'TPBank', 'Sacombank'] as $bank)
                <option value="{{ $bank }}">
            @endforeach
        </datalist>
        <div class="registry-actions"><button type="reset"
                class="registry-button registry-button-secondary">{{ __('registry.cancel') }}</button><button
                type="submit" class="registry-button"><x-admin::icon name="save" />{{ __('registry.save') }}</button>
        </div>
    </form>
@endsection
