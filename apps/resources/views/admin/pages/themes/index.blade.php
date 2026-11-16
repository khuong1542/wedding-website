@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/themes.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/themes.js')
@endpush
@section('description', __('appearance.description'))
@section('actions')
    <div class="appearance-heading-actions">
        <a class="appearance-button" href="{{ route('admin.themes.show', ['lang' => app()->getLocale()]) }}" target="_blank"
            rel="noopener"><x-admin::icon name="show" />{{ __('appearance.view') }}</a><button
            class="appearance-button is-primary" type="submit" form="appearance-form"><x-admin::icon
                name="save" />{{ __('appearance.save') }}</button>
    </div>
@endsection
@section('content')
    <div class="appearance-page" data-appearance data-error="{{ __('appearance.error') }}">
        <nav class="appearance-tabs" aria-label="{{ __('appearance.theme') }}">
            @foreach (['theme' => 'grid-alt', 'colors' => 'droplet', 'typography' => 'font', 'layout' => 'layout', 'advanced' => 'slider'] as $tab => $icon)
                <a href="#appearance-{{ $tab }}" class="appearance-button {{ $loop->first ? 'is-primary' : '' }}"
                    data-appearance-tab="{{ $tab }}"><x-admin::icon
                        :name="$icon" />{{ __('appearance.' . $tab) }}</a>
            @endforeach
        </nav>
        <div class="appearance-workspace">
            <form id="appearance-form" method="POST"
                action="{{ route('admin.themes.save', ['lang' => app()->getLocale()]) }}" data-appearance-form
                data-preview-url="{{ route('admin.themes.preview') }}"
                data-initial-preview="{{ route('admin.themes.show', ['lang' => app()->getLocale()]) }}">@csrf
                @method('PUT')
                <section class="admin-panel appearance-section" id="appearance-theme">
                    <header><x-admin::icon name="desktop" />
                        <div>
                            <h2>{{ __('appearance.choose') }}</h2>
                            <p>{{ __('appearance.choose_help') }}</p>
                        </div>
                    </header>
                    <div class="appearance-theme-grid">
                        @foreach ($themes as $theme => $colors)
                            <label class="appearance-theme"><input type="radio" name="theme"
                                    value="{{ $theme }}" @checked(old('theme', $settings['theme']) === $theme) data-appearance-theme
                                    data-colors="{{ json_encode($colors) }}"><span
                                    class="appearance-thumbnail appearance-thumbnail-{{ $theme }}"
                                    style="--thumb-primary: {{ $colors[0] }}; --thumb-secondary: {{ $colors[1] }}; --thumb-background: {{ $colors[3] }}; --thumb-text: {{ $colors[4] }}"><span
                                        class="appearance-thumbnail-ornament" aria-hidden="true">❧</span><span
                                        class="appearance-thumbnail-script">Our Wedding</span><span
                                        class="appearance-thumbnail-heart" aria-hidden="true">♡</span><span
                                        class="appearance-thumbnail-line"></span><span
                                        class="appearance-thumbnail-line is-short"></span><x-admin::icon
                                        name="check" /></span><strong>{{ __('appearance.' . $theme) }}</strong></label>
                        @endforeach
                    </div>
                </section>
                <section class="admin-panel appearance-section" id="appearance-colors">
                    <header><x-admin::icon name="palette" />
                        <div>
                            <h2>{{ __('appearance.palette') }}</h2>
                            <p>{{ __('appearance.palette_help') }}</p>
                        </div>
                    </header>
                    <div class="appearance-color-grid">
                        @foreach (['primary', 'secondary', 'accent', 'background', 'text', 'border'] as $color)
                            <div class="appearance-field"><label
                                    for="appearance-{{ $color }}">{{ __('appearance.' . $color) }}</label>
                                <div class="appearance-color-control"><input type="color"
                                        data-color-picker="{{ $color }}"
                                        aria-label="{{ __('appearance.' . $color) }}"
                                        value="{{ old($color, $settings[$color]) }}"><input type="text"
                                        name="{{ $color }}" id="appearance-{{ $color }}"
                                        value="{{ old($color, $settings[$color]) }}" pattern="#[a-fA-F0-9]{6}"
                                        maxlength="7" required data-color-value="{{ $color }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <p>{{ __('appearance.suggestions') }}</p>
                    <div class="appearance-palettes">
                        @foreach ($themes as $theme => $colors)
                            <button class="appearance-palette" type="button"
                                data-appearance-palette="{{ json_encode($colors) }}"
                                aria-label="{{ __('appearance.' . $theme) }}">
                                @foreach (array_slice($colors, 0, 4) as $color)
                                    <span style="background: {{ $color }}"></span>
                                @endforeach
                            </button>
                        @endforeach
                    </div>
                </section>
                <section class="admin-panel appearance-section" id="appearance-typography">
                    <header><x-admin::icon name="font" />
                        <div>
                            <h2>{{ __('appearance.typography') }}</h2>
                            <p>{{ __('appearance.font_help') }}</p>
                        </div>
                    </header>
                    <div class="appearance-font-grid">
                        @foreach (['heading_font', 'body_font'] as $field)
                            <div class="appearance-field"><label
                                    for="appearance-{{ $field }}">{{ __('appearance.' . $field) }}</label><select
                                    id="appearance-{{ $field }}" name="{{ $field }}">
                                    @foreach ($fonts as $key => $font)
                                        <option value="{{ $key }}" @selected(old($field, $settings[$field]) === $key)>
                                            {{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <div class="appearance-field">
                            <label for="appearance-font-size">{{ __('appearance.font_size') }}</label><select
                                id="appearance-font-size" name="font_size">
                                @foreach (range(12, 20) as $size)
                                    <option value="{{ $size }}" @selected((int) old('font_size', $settings['font_size']) === $size)>{{ $size }}px
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="appearance-type-sample"><strong data-heading-sample>{{ __('appearance.sample') }}</strong>
                        <p data-body-sample>{{ __('appearance.sample_body') }}</p>
                    </div>
                </section>
                <section class="admin-panel appearance-section" id="appearance-layout">
                    <header><x-admin::icon name="layout" />
                        <h2>{{ __('appearance.layout') }}</h2>
                    </header>
                    <div class="appearance-font-grid">
                        @foreach (['content_width' => [960, 1600], 'radius' => [0, 32]] as $field => $range)
                            <div class="appearance-field"><label
                                    for="appearance-{{ $field }}">{{ __('appearance.' . $field) }}</label><input
                                    type="number" id="appearance-{{ $field }}" name="{{ $field }}"
                                    min="{{ $range[0] }}" max="{{ $range[1] }}"
                                    value="{{ old($field, $settings[$field]) }}" required></div>
                        @endforeach
                    </div>
                </section>
                <section class="admin-panel appearance-section" id="appearance-advanced">
                    <header><x-admin::icon name="slider" />
                        <h2>{{ __('appearance.advanced') }}</h2>
                    </header>
                    @foreach (['sticky_header', 'animations'] as $field)
                        <div class="appearance-field"><input type="hidden" name="{{ $field }}"
                                value="0"><label class="appearance-switch-label"
                                for="appearance-{{ $field }}">{{ __('appearance.' . $field) }}<input
                                    type="checkbox" role="switch" class="appearance-switch" name="{{ $field }}"
                                    id="appearance-{{ $field }}" value="1"
                                    @checked(old($field, $settings[$field]))></label></div>
                    @endforeach
                </section>
                <div class="appearance-form-actions"><button class="appearance-button"
                        type="reset">{{ __('appearance.reset') }}</button><button class="appearance-button is-primary"
                        type="submit"><x-admin::icon name="save" />{{ __('appearance.save') }}</button></div>
            </form>
            <aside class="admin-panel appearance-preview">
                <header>
                    <h2><x-admin::icon name="desktop" />{{ __('appearance.preview') }}</h2>
                    <div class="appearance-devices" role="group" aria-label="{{ __('appearance.preview') }}">
                        @foreach (['desktop' => 'desktop', 'tablet' => 'tablet', 'mobile' => 'mobile'] as $device => $icon)
                            <button type="button" data-appearance-device="{{ $device }}"
                                aria-label="{{ __('appearance.' . $device) }}"
                                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><x-admin::icon
                                    :name="$icon" /></button>
                        @endforeach
                    </div>
                </header>
                <p data-appearance-loading role="status">{{ __('appearance.loading') }}</p>
                <div class="appearance-preview-viewport" data-appearance-viewport
                    data-preview-title="{{ __('appearance.preview') }}"></div><button type="button"
                    class="appearance-button" data-appearance-retry hidden>{{ __('appearance.retry') }}</button>
            </aside>
        </div>
    </div>
@endsection
