@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/page-builder.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/page-builder.js')
@endpush
@section('description', __('builder.description'))
@section('actions')
    <div class="pb-actions">
        <button class="pb-button" type="button" data-pb-preview><x-admin::icon
                name="show" />{{ __('builder.preview') }}</button><button class="pb-button" type="button"
            data-pb-save="draft"><x-admin::icon name="save" />{{ __('builder.draft') }}</button><button
            class="pb-button is-primary" type="button" data-pb-save="publish">{{ __('builder.publish') }}</button>
    </div>
@endsection
@section('content')
    <div class="pb-page" data-page-builder data-save-url="{{ route('admin.page-builder.save') }}"
        data-preview-url="{{ route('admin.page-builder.preview') }}" data-revision="{{ $revision }}">
        <script type="application/json" data-pb-seed>@json(['blocks' => $blocks, 'labels' => __('builder'), 'types' => $blockTypes])</script>
        <div class="admin-panel pb-toolbar">
            <div><strong>{{ __('builder.current_page') }}:</strong> {{ $contentPage?->title ?? __('builder.no_page') }}<a
                    class="pb-button" href="{{ route('admin.pages.index', ['lang' => app()->getLocale()]) }}"><x-admin::icon
                        name="cog" />{{ __('builder.page_settings') }}</a></div>
            <div class="pb-actions">
                <div class="pb-devices" role="group" aria-label="{{ __('builder.preview') }}">
                    @foreach (['desktop' => 'desktop', 'tablet' => 'tablet', 'mobile' => 'mobile'] as $device => $icon)
                        <button type="button" data-pb-device="{{ $device }}"
                            aria-label="{{ __('builder.' . $device) }}"
                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><x-admin::icon
                                :name="$icon" /></button>
                    @endforeach
                </div>
                <button class="pb-button" type="button" data-pb-undo aria-label="{{ __('builder.undo') }}"
                    disabled><x-admin::icon name="undo" /></button><button class="pb-button" type="button" data-pb-redo
                    aria-label="{{ __('builder.redo') }}" disabled><x-admin::icon name="redo" /></button>
            </div>
        </div>
        <div class="pb-status"><span data-pb-status role="status">{{ $builder ? __('builder.clean') : '' }}</span><a
                href="{{ route('admin.page-builder.show', ['version' => 'published', 'lang' => app()->getLocale()]) }}"
                target="_blank" rel="noopener" data-pb-published
                @if (!$builder?->published_at) hidden @endif>{{ __('builder.view_published') }}</a></div>
        <div class="pb-workspace">
            <aside class="admin-panel pb-library">
                <div class="pb-library-tabs" role="tablist">
                    @foreach (['blocks', 'layouts'] as $tab)
                        <button type="button" role="tab" data-pb-library-tab="{{ $tab }}"
                            id="pb-tab-{{ $tab }}" aria-controls="pb-library-{{ $tab }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ __('builder.' . $tab) }}</button>
                    @endforeach
                </div>
                <div id="pb-library-blocks" role="tabpanel" aria-labelledby="pb-tab-blocks" data-pb-library="blocks">
                    <div class="pb-block-grid">
                        @foreach ($blockTypes as $type => $icon)
                            <button type="button" draggable="true" data-pb-add="{{ $type }}"><x-admin::icon
                                    :name="$icon" /><span>{{ __('builder.' . $type) }}</span></button>
                        @endforeach
                    </div>
                </div>
                <div id="pb-library-layouts" role="tabpanel" aria-labelledby="pb-tab-layouts" data-pb-library="layouts"
                    hidden><button class="pb-layout" type="button" data-pb-layout="starter"><x-admin::icon
                            name="layout" />{{ __('builder.starter') }}</button><button class="pb-layout" type="button"
                        data-pb-layout="article"><x-admin::icon name="align-left" />{{ __('builder.article') }}</button>
                </div>
            </aside>
            <section class="admin-panel pb-canvas-panel">
                <div class="pb-canvas-viewport">
                    <div class="pb-canvas" data-pb-canvas data-device="desktop" aria-label="{{ __('builder.blocks') }}">
                    </div>
                </div><button type="button" class="pb-button pb-add" data-pb-add="text"><x-admin::icon
                        name="plus" />{{ __('builder.add') }}</button>
            </section>
            <aside class="admin-panel pb-inspector">
                <header>
                    <h2>{{ __('builder.settings') }}</h2><button type="button" data-pb-deselect
                        aria-label="{{ __('builder.close') }}"><x-admin::icon name="x" /></button>
                </header>
                <p class="pb-no-selection" data-pb-no-selection>{{ __('builder.select') }}</p>
                <form data-pb-inspector hidden>@csrf<div class="pb-inspector-tabs" role="tablist">
                        @foreach (['general', 'style', 'advanced'] as $tab)
                            <button type="button" role="tab" data-pb-inspector-tab="{{ $tab }}"
                                id="pb-inspector-tab-{{ $tab }}"
                                aria-controls="pb-inspector-panel-{{ $tab }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ __('builder.' . $tab) }}</button>
                        @endforeach
                    </div>
                    <div class="pb-inspector-panel" data-pb-panel="general" id="pb-inspector-panel-general" role="tabpanel"
                        aria-labelledby="pb-inspector-tab-general">
                        @foreach (['name' => 120, 'title' => 120, 'url' => 2048, 'button_label' => 80] as $field => $limit)
                            <div class="pb-field" data-pb-field="{{ $field }}"><label
                                    for="pb-{{ $field }}">{{ __('builder.' . $field) }}</label><input
                                    id="pb-{{ $field }}" name="{{ $field }}"
                                    maxlength="{{ $limit }}" type="{{ $field === 'url' ? 'url' : 'text' }}"
                                    @required($field === 'name')></div>
                        @endforeach
                        <div class="pb-field" data-pb-field="text">
                            <label for="pb-text">{{ __('builder.text') }}</label>
                            <textarea id="pb-text" name="text" rows="6" maxlength="3000"></textarea>
                        </div>
                        <div class="pb-field"><label for="pb-align">{{ __('builder.align') }}</label><select
                                id="pb-align" name="align">
                                @foreach (['left', 'center', 'right'] as $align)
                                    <option value="{{ $align }}">{{ __('builder.' . $align) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pb-inspector-panel" data-pb-panel="style" id="pb-inspector-panel-style" role="tabpanel"
                        aria-labelledby="pb-inspector-tab-style" hidden>
                        <div class="pb-field"><label for="pb-background">{{ __('builder.background') }}</label><input
                                type="color" name="background" id="pb-background"></div>
                        @foreach (['height' => 900, 'padding' => 120] as $field => $max)
                            <div class="pb-field"><label
                                    for="pb-{{ $field }}">{{ __('builder.' . $field) }}</label><input
                                    type="number" name="{{ $field }}" id="pb-{{ $field }}"
                                    min="0" max="{{ $max }}" required></div>
                        @endforeach
                    </div>
                    <div class="pb-inspector-panel" data-pb-panel="advanced" id="pb-inspector-panel-advanced"
                        role="tabpanel" aria-labelledby="pb-inspector-tab-advanced" hidden>
                        <h3>{{ __('builder.visibility') }}</h3>
                        @foreach (['desktop', 'tablet', 'mobile'] as $device)
                            <div class="pb-field"><label class="pb-switch-label"
                                    for="pb-{{ $device }}">{{ __('builder.' . $device) }}<input type="checkbox"
                                        name="{{ $device }}" id="pb-{{ $device }}" role="switch"
                                        class="pb-switch"></label></div>
                        @endforeach
                        <div class="pb-field">
                            <label for="pb-animation">{{ __('builder.animation') }}</label><select name="animation"
                                id="pb-animation">
                                @foreach (['none', 'fade'] as $effect)
                                    <option value="{{ $effect }}">{{ __('builder.' . $effect) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pb-field"><label for="pb-duration">{{ __('builder.duration') }}</label><input
                                type="number" id="pb-duration" name="duration" min="100" max="2000" required>
                        </div>
                    </div>
                </form>
            </aside>
        </div>
        <dialog class="pb-preview-dialog" data-pb-dialog aria-label="{{ __('builder.preview') }}"><button
                class="pb-button" type="button" data-pb-close-preview>{{ __('builder.close') }}</button>
            <p role="status" data-pb-preview-status>{{ __('builder.loading') }}</p>
            <div data-pb-preview-content></div>
        </dialog>
    </div>
@endsection
