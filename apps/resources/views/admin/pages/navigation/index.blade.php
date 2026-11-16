@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/navigation.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/navigation.js')
@endpush
@section('description', __('navigation.description'))
@section('actions')
    <div class="nf-actions">
        <a class="nf-button" href="{{ route('admin.navigation.show', ['lang' => app()->getLocale()]) }}" target="_blank"
            rel="noopener"><x-admin::icon name="show" />{{ __('navigation.view') }}</a><button class="nf-button is-primary"
            form="navigation-form" type="submit"><x-admin::icon name="save" />{{ __('navigation.save') }}</button>
    </div>
@endsection
@section('content')
    <div class="nf-page" data-navigation data-error="{{ __('navigation.error') }}">
        @php
            $navigationSeed = [
                'menu' => old('menu', $settings['menu']),
                'footer_links' => old('footer_links', $settings['footer_links']),
                'icons' => $icons,
                'labels' => __('navigation'),
            ];
        @endphp
        <script type="application/json" data-nf-seed>@json($navigationSeed)</script>
        <nav class="nf-tabs" aria-label="{{ __('navigation.menu') }}">
            @foreach (['menu' => 'menu', 'footer' => 'layout', 'display' => 'cog'] as $tab => $icon)
                <a class="nf-button {{ $loop->first ? 'is-primary' : '' }}" href="#nf-{{ $tab }}"
                    data-nf-tab><x-admin::icon :name="$icon" />{{ __('navigation.' . $tab) }}</a>
            @endforeach
        </nav>
        <form id="navigation-form" method="POST"
            action="{{ route('admin.navigation.save', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data"
            data-nf-form data-preview-url="{{ route('admin.navigation.preview') }}">@csrf @method('PUT')<input
                type="hidden" name="menu" data-nf-menu><input type="hidden" name="footer_links" data-nf-links>
            <div class="nf-workspace">
                <div class="nf-controls">
                    <section class="admin-panel nf-section" id="nf-menu">
                        <header>
                            <div>
                                <h2><x-admin::icon name="menu" />{{ __('navigation.manage') }}</h2>
                                <p>{{ __('navigation.manage_help') }}</p>
                            </div><button class="nf-button is-primary" type="button" data-nf-add="menu"><x-admin::icon
                                    name="plus" />{{ __('navigation.add') }}</button>
                        </header>
                        <div class="nf-table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('navigation.label') }}</th>
                                        <th scope="col">{{ __('navigation.url') }}</th>
                                        <th scope="col">{{ __('navigation.visible') }}</th>
                                        <th scope="col">{{ __('navigation.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody data-nf-list="menu"></tbody>
                            </table>
                        </div>
                    </section>
                    <section class="admin-panel nf-section" id="nf-display">
                        <header>
                            <h2><x-admin::icon name="cog" />{{ __('navigation.display') }}</h2>
                        </header>
                        <div class="nf-field-grid">
                            @foreach (['layout' => ['horizontal', 'stacked'], 'logo_position' => ['left', 'center']] as $field => $options)
                                <div class="nf-field"><label
                                        for="nf-{{ $field }}">{{ __('navigation.' . $field) }}</label><select
                                        name="display[{{ $field }}]" id="nf-{{ $field }}">
                                        @foreach ($options as $option)
                                            <option value="{{ $option }}" @selected(old('display.' . $field, $settings['display'][$field]) === $option)>
                                                {{ __('navigation.' . $option) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        @foreach (['show_icons', 'new_tab', 'show_footer'] as $field)
                            <div class="nf-field"><input type="hidden" name="display[{{ $field }}]"
                                    value="0"><label class="nf-switch-label" for="nf-{{ $field }}"><input
                                        class="nf-switch" type="checkbox" name="display[{{ $field }}]"
                                        id="nf-{{ $field }}" value="1" role="switch"
                                        @checked(old('display.' . $field, $settings['display'][$field]))>{{ __('navigation.' . $field) }}</label></div>
                        @endforeach
                    </section>
                </div>
                <div class="nf-preview-column">
                    <section class="admin-panel nf-section nf-preview">
                        <div class="nf-preview-status">
                            <p role="status" data-nf-loading>{{ __('navigation.loading') }}</p><button class="nf-button"
                                type="button" data-nf-preview>{{ __('navigation.preview') }}</button>
                        </div>
                        <div data-nf-preview-content></div>
                    </section>
                    <section class="admin-panel nf-section" id="nf-footer">
                        <header>
                            <h2><x-admin::icon name="layout" />{{ __('navigation.footer') }}</h2>
                        </header>
                        <div class="nf-footer-tabs" role="tablist">
                            @foreach (['content', 'quick_links', 'socials', 'contact'] as $tab)
                                <button type="button" role="tab" data-nf-footer-tab="{{ $tab }}"
                                    id="nf-tab-{{ $tab }}" aria-controls="nf-panel-{{ $tab }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ __('navigation.' . $tab) }}</button>
                            @endforeach
                        </div>
                        <div data-nf-panel="content" id="nf-panel-content" role="tabpanel" aria-labelledby="nf-tab-content">
                            <div class="nf-field-grid">
                                @foreach (['brand' => 120, 'copyright' => 255] as $field => $max)
                                    <div class="nf-field"><label
                                            for="nf-footer-{{ $field }}">{{ __('navigation.' . $field) }}{{ $field === 'brand' ? ' *' : '' }}</label><input
                                            id="nf-footer-{{ $field }}" name="footer[{{ $field }}]"
                                            value="{{ old('footer.' . $field, $settings['footer'][$field]) }}"
                                            maxlength="{{ $max }}" @required($field === 'brand')></div>
                                @endforeach
                            </div>
                            <div class="nf-field-grid">
                                <div class="nf-field"><label
                                        for="nf-description">{{ __('navigation.footer_description') }}</label>
                                    <textarea id="nf-description" name="footer[description]" rows="5" maxlength="1000">{{ old('footer.description', $settings['footer']['description']) }}</textarea>
                                </div>
                                <div class="nf-field"><label>{{ __('navigation.logo') }}</label>
                                    <div class="nf-logo-picker"><label for="nf-logo" class="nf-logo-frame"><span
                                                data-nf-logo-hint><x-admin::icon
                                                    name="image" />{{ __('navigation.logo_hint') }}</span><img
                                                data-nf-logo-image alt="{{ __('navigation.logo') }}"
                                                @if ($settings['logo'] && !old('remove_logo')) src="{{ route('admin.navigation.logo') }}" @else hidden @endif><input
                                                id="nf-logo" name="logo" type="file"
                                                accept="image/jpeg,image/png,image/webp"></label><input type="hidden"
                                            name="remove_logo" value="{{ old('remove_logo', 0) }}"><button
                                            type="button" data-nf-remove-logo
                                            aria-label="{{ __('navigation.remove_logo') }}"><x-admin::icon
                                                name="x" /></button></div>
                                </div>
                            </div>
                        </div>
                        <div data-nf-panel="quick_links" id="nf-panel-quick_links" role="tabpanel"
                            aria-labelledby="nf-tab-quick_links" hidden><button class="nf-button" type="button"
                                data-nf-add="footer_links"><x-admin::icon
                                    name="plus" />{{ __('navigation.add') }}</button>
                            <div class="nf-table-scroll">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">{{ __('navigation.label') }}</th>
                                            <th scope="col">{{ __('navigation.url') }}</th>
                                            <th scope="col">{{ __('navigation.visible') }}</th>
                                            <th scope="col">{{ __('navigation.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody data-nf-list="footer_links"></tbody>
                                </table>
                            </div>
                        </div>
                        <div data-nf-panel="socials" id="nf-panel-socials" role="tabpanel"
                            aria-labelledby="nf-tab-socials" hidden>
                            @foreach (['facebook', 'instagram', 'youtube', 'tiktok'] as $social)
                                <div class="nf-field"><label
                                        for="nf-{{ $social }}">{{ ucfirst($social) }}</label><input type="url"
                                        name="footer[{{ $social }}]" id="nf-{{ $social }}"
                                        value="{{ old('footer.' . $social, $settings['footer'][$social]) }}"
                                        maxlength="1000" placeholder="https://"></div>
                            @endforeach
                        </div>
                        <div data-nf-panel="contact" id="nf-panel-contact" role="tabpanel"
                            aria-labelledby="nf-tab-contact" hidden>
                            @foreach (['email' => 255, 'phone' => 30] as $field => $max)
                                <div class="nf-field"><label
                                        for="nf-{{ $field }}">{{ __('navigation.' . $field) }}</label><input
                                        type="{{ $field === 'email' ? 'email' : 'text' }}"
                                        name="footer[{{ $field }}]" id="nf-{{ $field }}"
                                        value="{{ old('footer.' . $field, $settings['footer'][$field]) }}"
                                        maxlength="{{ $max }}"></div>
                            @endforeach
                            <div class="nf-field"><label for="nf-address">{{ __('navigation.address') }}</label>
                                <textarea id="nf-address" name="footer[address]" rows="3" maxlength="500">{{ old('footer.address', $settings['footer']['address']) }}</textarea>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="nf-form-actions"><button class="nf-button"
                    type="reset">{{ __('navigation.cancel') }}</button><button class="nf-button is-primary"
                    type="submit"><x-admin::icon name="save" />{{ __('navigation.save') }}</button></div>
        </form>
        <dialog class="nf-dialog" data-nf-confirm aria-labelledby="nf-confirm-title">
            <h2 id="nf-confirm-title">{{ __('navigation.confirm') }}</h2><strong data-nf-delete-name></strong>
            <div class="nf-actions"><button class="nf-button" type="button"
                    data-nf-close>{{ __('navigation.close') }}</button><button class="nf-button is-primary"
                    type="button" data-nf-confirm-delete>{{ __('navigation.delete') }}</button></div>
        </dialog>
    </div>
@endsection
