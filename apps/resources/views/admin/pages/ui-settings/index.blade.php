@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/ui-settings.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/ui-settings.js')
@endpush
@section('description', __('ui-settings.description'))
@section('actions')
    <div class="appearance-heading-actions">
        <a class="appearance-button" href="{{ route('admin.ui-settings.show', ['lang' => app()->getLocale()]) }}"
            target="_blank" rel="noopener"><x-admin::icon name="show" />{{ __('appearance.view') }}</a><button
            class="appearance-button is-primary" type="submit" form="appearance-form"><x-admin::icon
                name="save" />{{ __('appearance.save') }}</button>
    </div>
@endsection
@section('content')
    <div class="appearance-page ui-settings-page" data-appearance data-error="{{ __('appearance.error') }}">
        <nav class="appearance-tabs" aria-label="{{ __('ui-settings.general') }}">
            @foreach (['general' => 'edit', 'colors' => 'droplet', 'typography' => 'font', 'components' => 'layout', 'effects' => 'sparkles', 'advanced' => 'slider'] as $tab => $icon)
                <a href="#ui-{{ $tab }}" class="appearance-button {{ $loop->first ? 'is-primary' : '' }}"
                    data-appearance-tab="{{ $tab }}"><x-admin::icon
                        :name="$icon" />{{ __('ui-settings.' . $tab) }}</a>
            @endforeach
        </nav>
        <form id="appearance-form" method="POST"
            action="{{ route('admin.ui-settings.save', ['lang' => app()->getLocale()]) }}" data-appearance-form
            data-preview-url="{{ route('admin.ui-settings.preview') }}">@csrf @method('PUT')
            <input type="hidden" name="theme" value="{{ old('theme', $settings['theme']) }}">
            <div class="ui-settings-grid">
                <div class="ui-settings-controls">
                    <section class="admin-panel appearance-section" id="ui-general">
                        <header><x-admin::icon name="desktop" />
                            <div>
                                <h2>{{ __('ui-settings.mode') }}</h2>
                                <p>{{ __('ui-settings.mode_help') }}</p>
                            </div>
                        </header>
                        <div class="ui-option-grid">
                            @foreach (['light' => 'sun', 'dark' => 'moon', 'system' => 'desktop'] as $mode => $icon)
                                <label class="ui-option"><input type="radio" name="mode" value="{{ $mode }}"
                                        @checked(old('mode', $settings['mode']) === $mode)><x-admin::icon
                                        :name="$icon" /><span>{{ __('ui-settings.' . $mode) }}</span><x-admin::icon
                                        name="check" class="ui-option-check" /></label>
                            @endforeach
                        </div>
                        <hr>
                        <header><x-admin::icon name="layout" />
                            <div>
                                <h2>{{ __('ui-settings.layout') }}</h2>
                                <p>{{ __('ui-settings.layout_help') }}</p>
                            </div>
                        </header>
                        <div class="ui-option-grid">
                            @foreach (['full', 'boxed', 'wide'] as $layout)
                                <label class="ui-option"><input type="radio" name="layout" value="{{ $layout }}"
                                        @checked(old('layout', $settings['layout']) === $layout)><span
                                        class="ui-layout-demo ui-layout-{{ $layout }}"><span></span></span><span>{{ __('ui-settings.' . $layout) }}</span><x-admin::icon
                                        name="check" class="ui-option-check" /></label>
                            @endforeach
                        </div>
                    </section>
                    <section class="admin-panel appearance-section" id="ui-components">
                        <header><x-admin::icon name="shape-square" />
                            <h2>{{ __('ui-settings.radius') }}</h2>
                        </header>
                        <div class="appearance-field ui-range-field"><label class="sr-only"
                                for="ui-radius">{{ __('ui-settings.radius') }}</label><input type="range" min="0"
                                max="32" value="{{ old('radius', $settings['radius']) }}" data-ui-range="radius"
                                aria-label="{{ __('ui-settings.radius') }}">
                            <div><input type="number" name="radius" id="ui-radius" min="0" max="32"
                                    value="{{ old('radius', $settings['radius']) }}" required>px</div>
                        </div>
                        <hr>
                        <header><x-admin::icon name="square" />
                            <h2>{{ __('ui-settings.shadow') }}</h2>
                        </header>
                        <div class="ui-option-grid ui-shadow-options">
                            @foreach (['none', 'soft', 'medium', 'strong'] as $shadow)
                                <label class="ui-option"><input type="radio" name="shadow" value="{{ $shadow }}"
                                        @checked(old('shadow', $settings['shadow']) === $shadow)><span
                                        class="ui-shadow-demo ui-shadow-{{ $shadow }}"></span><span>{{ __('ui-settings.' . $shadow) }}</span></label>
                            @endforeach
                        </div>
                        <hr>
                        <header><x-admin::icon name="expand-horizontal" />
                            <h2>{{ __('ui-settings.spacing') }}</h2>
                        </header>
                        <div class="appearance-field ui-range-field"><label class="sr-only"
                                for="ui-spacing">{{ __('ui-settings.spacing') }}</label><input type="range"
                                min="0" max="40" value="{{ old('spacing', $settings['spacing']) }}"
                                data-ui-range="spacing" aria-label="{{ __('ui-settings.spacing') }}">
                            <div><input type="number" name="spacing" id="ui-spacing" min="0" max="40"
                                    value="{{ old('spacing', $settings['spacing']) }}" required>px</div>
                        </div>
                        @foreach (['button_style' => ['filled', 'outline'], 'button_size' => ['small', 'medium', 'large']] as $field => $options)
                            <div class="appearance-field ui-select-field"><label
                                    for="ui-{{ $field }}">{{ __('ui-settings.' . $field) }}</label><select
                                    id="ui-{{ $field }}" name="{{ $field }}">
                                    @foreach ($options as $option)
                                        <option value="{{ $option }}" @selected(old($field, $settings[$field]) === $option)>
                                            {{ __('ui-settings.' . $option) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </section>
                    <section class="admin-panel appearance-section" id="ui-typography">
                        <header><x-admin::icon name="font" />
                            <div>
                                <h2>{{ __('ui-settings.typography') }}</h2>
                                <p>{{ __('appearance.font_help') }}</p>
                            </div>
                        </header>
                        @foreach (['heading_font', 'body_font'] as $field)
                            <div class="appearance-field ui-select-field"><label
                                    for="ui-{{ $field }}">{{ __('appearance.' . $field) }}</label><select
                                    id="ui-{{ $field }}" name="{{ $field }}">
                                    @foreach ($fonts as $key => $font)
                                        <option value="{{ $key }}" @selected(old($field, $settings[$field]) === $key)>
                                            {{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <div class="appearance-field ui-select-field">
                            <label for="ui-font-size">{{ __('appearance.font_size') }}</label><select name="font_size"
                                id="ui-font-size">
                                @foreach (range(12, 20) as $size)
                                    <option value="{{ $size }}" @selected((int) old('font_size', $settings['font_size']) === $size)>
                                        {{ $size }}px</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="appearance-type-sample"><strong
                                data-heading-sample>{{ __('appearance.sample') }}</strong>
                            <p data-body-sample>{{ __('appearance.sample_body') }}</p>
                        </div>
                    </section>
                </div>
                <div class="ui-settings-preview-column">
                    <section class="admin-panel appearance-preview">
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
                        <div class="ui-preview-browser"><span aria-hidden="true">● ● ●</span>
                            <div class="appearance-preview-viewport" data-appearance-viewport
                                data-preview-title="{{ __('appearance.preview') }}"></div>
                        </div><button type="button" class="appearance-button" data-appearance-retry
                            hidden>{{ __('appearance.retry') }}</button>
                    </section>
                    <section class="admin-panel appearance-section" id="ui-colors">
                        <header><x-admin::icon name="palette" />
                            <div>
                                <h2>{{ __('appearance.palette') }}</h2>
                                <p>{{ __('appearance.palette_help') }}</p>
                            </div>
                        </header>
                        <div class="ui-preset-grid">
                            @foreach ($themes as $theme => $colors)
                                <button type="button" class="ui-preset" data-ui-theme="{{ $theme }}"
                                    data-appearance-palette="{{ json_encode($colors) }}"
                                    aria-pressed="{{ old('theme', $settings['theme']) === $theme ? 'true' : 'false' }}"><span>
                                        @foreach (array_slice($colors, 0, 3) as $color)
                                            <i style="background: {{ $color }}"></i>
                                        @endforeach
                                    </span>
                                    {{ __('appearance.' . $theme) }}</button>
                            @endforeach
                        </div>
                        <hr>
                        <div class="appearance-color-grid">
                            @foreach (['primary', 'secondary', 'accent', 'background', 'text', 'border'] as $color)
                                <div class="appearance-field"><label
                                        for="ui-{{ $color }}">{{ __('appearance.' . $color) }}</label>
                                    <div class="appearance-color-control"><input type="color"
                                            value="{{ old($color, $settings[$color]) }}"
                                            data-color-picker="{{ $color }}"
                                            aria-label="{{ __('appearance.' . $color) }}"><input type="text"
                                            name="{{ $color }}" id="ui-{{ $color }}" maxlength="7"
                                            pattern="#[a-fA-F0-9]{6}" value="{{ old($color, $settings[$color]) }}"
                                            data-color-value="{{ $color }}" required></div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    <section class="admin-panel appearance-section" id="ui-effects">
                        <header><x-admin::icon name="sparkles" />
                            <h2>{{ __('ui-settings.effects') }}</h2>
                        </header>
                        @foreach (['animations', 'hover_effect', 'smooth_scroll'] as $field)
                            <div class="appearance-field"><input type="hidden" name="{{ $field }}"
                                    value="0"><label class="appearance-switch-label"
                                    for="ui-{{ $field }}">{{ __('ui-settings.' . $field) }}<input type="checkbox"
                                        name="{{ $field }}" id="ui-{{ $field }}" value="1"
                                        class="appearance-switch" role="switch" @checked(old($field, $settings[$field]))></label>
                            </div>
                        @endforeach
                    </section>
                    <section class="admin-panel appearance-section" id="ui-advanced">
                        <header><x-admin::icon name="slider" />
                            <h2>{{ __('ui-settings.advanced') }}</h2>
                        </header>
                        <div class="appearance-field"><label
                                for="ui-content-width">{{ __('appearance.content_width') }}</label><input type="number"
                                name="content_width" id="ui-content-width" min="960" max="1600"
                                value="{{ old('content_width', $settings['content_width']) }}" required></div>
                        <div class="appearance-field"><input type="hidden" name="sticky_header" value="0"><label
                                class="appearance-switch-label"
                                for="ui-sticky-header">{{ __('ui-settings.sticky_header') }}<input
                                    class="appearance-switch" type="checkbox" role="switch" name="sticky_header"
                                    id="ui-sticky-header" value="1" @checked(old('sticky_header', $settings['sticky_header']))></label></div>
                    </section>
                </div>
            </div>
            <div class="appearance-form-actions"><button class="appearance-button"
                    type="reset">{{ __('appearance.reset') }}</button><button class="appearance-button is-primary"
                    type="submit"><x-admin::icon name="save" />{{ __('appearance.save') }}</button></div>
        </form>
    </div>
@endsection
