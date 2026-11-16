@extends('admin.layouts.index')
@push('styles')
    @vite('resources/css/admin/pages/content-pages.css')
@endpush
@push('scripts')
    @vite('resources/js/admin/pages/content-pages.js')
@endpush
@section('description', __('pages.description'))
@section('content')
    <div class="builder" data-builder data-error="{{ __('pages.error') }}" data-status-saved="{{ __('pages.status_saved') }}"
        data-photo-error="{{ __('pages.photo_error') }}" data-limit="{{ __('pages.limit') }}">
        <div class="builder-workspace" data-pages-workspace>
            <section class="admin-panel builder-editor">
                <h2>{{ $selectedPage?->title ?? __('pages.layout') }}</h2>
                <form id="builder-form" data-builder-form data-preview-url="{{ route('admin.pages.preview') }}" method="POST"
                    action="{{ route('admin.pages.save', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data">
                    @csrf<input type="hidden" name="id" value="{{ $selectedPage?->id }}">
                    <div class="builder-editor-tabs" role="tablist" aria-label="{{ __('pages.layout') }}">
                        @foreach (['layout', 'general', 'seo'] as $tab)
                            <button type="button" role="tab" data-tab="{{ $tab }}"
                                id="builder-tab-{{ $tab }}" aria-controls="builder-panel-{{ $tab }}"
                                aria-selected="{{ $tab === 'layout' ? 'true' : 'false' }}"
                                tabindex="{{ $tab === 'layout' ? 0 : -1 }}">{{ __('pages.' . $tab) }}</button>
                        @endforeach
                        <button type="button" class="builder-preview-button" data-preview>
                            <x-admin::icon name="desktop" />{{ __('pages.preview') }}</button>
                    </div>
                    <div data-panel="layout" id="builder-panel-layout" role="tabpanel" aria-labelledby="builder-tab-layout"
                        class="builder-layout">
                        <div class="builder-template-picker">
                            <h3>{{ __('pages.choose_template') }}</h3>
                            @foreach ($pageTemplates as $templateKey => $definition)
                                <label class="builder-template-card">
                                    @if ($loop->first)
                                        <span class="builder-template-default">{{ __('pages.template_default') }}</span>
                                    @endif
                                    <input type="radio" name="blade_template" value="{{ $templateKey }}"
                                        @checked(old('blade_template', $selectedPage?->blade_template ?? 'classic') === $templateKey) required>
                                    <span
                                        class="builder-template-illustration builder-template-{{ $templateKey }}"><x-admin::icon
                                            :name="$definition['icon']" /></span>
                                    <strong>{{ __('pages.template_number', ['number' => $loop->iteration]) }}</strong>
                                    <span>{{ __('pages.' . $definition['description']) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div data-panel="general" id="builder-panel-general" role="tabpanel"
                        aria-labelledby="builder-tab-general" class="builder-general" hidden>
                        <div class="builder-field">
                            <label for="page-title">{{ __('pages.title') }} *</label>
                            <input id="page-title" name="title" value="{{ old('title', $selectedPage?->title ?? '') }}"
                                maxlength="120" required>
                        </div>
                        <div class="builder-couple-name-fields">
                            @foreach (['groom_name', 'bride_name'] as $field)
                                <div class="builder-field">
                                    <label for="page-{{ $field }}">{{ __('pages.' . $field) }}</label>
                                    <input id="page-{{ $field }}" name="{{ $field }}"
                                        value="{{ old($field, $selectedPage?->$field ?? '') }}" maxlength="120"
                                        placeholder="{{ __('pages.' . $field . '_placeholder') }}">
                                </div>
                            @endforeach
                        </div>
                        <div class="builder-logo-label">{{ __('pages.logo') }}</div>
                        <div class="builder-photo"><label class="builder-photo-frame" for="page-photo"><span
                                    data-photo-hint><x-admin::icon
                                        name="camera-plus" />{{ __('pages.photo_help') }}</span><img data-page-image
                                    alt="{{ __('pages.logo') }}"
                                    @if ($selectedPage?->photo && !old('remove_photo')) src="{{ route('admin.pages.photo', $selectedPage) }}" @else hidden @endif><input
                                    id="page-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                                    data-page-photo aria-label="{{ __('pages.logo') }}"></label><input type="hidden"
                                name="remove_photo" value="{{ old('remove_photo', '0') }}" data-remove-photo-value><button
                                type="button" data-remove-photo aria-label="{{ __('pages.remove_photo') }}"><x-admin::icon
                                    name="x" /></button>
                        </div>
                    </div>
                    <div data-panel="seo" id="builder-panel-seo" role="tabpanel" aria-labelledby="builder-tab-seo"
                        class="builder-general" hidden>
                        @foreach (['seo_title' => 160, 'seo_description' => 320, 'canonical_url' => 1000] as $field => $max)
                            <div class="builder-field"><label
                                    for="page-{{ $field }}">{{ __('pages.' . $field) }}</label><input
                                    id="page-{{ $field }}" name="{{ $field }}"
                                    type="{{ $field === 'canonical_url' ? 'url' : 'text' }}"
                                    value="{{ old($field, $selectedPage?->$field ?? '') }}"
                                    maxlength="{{ $max }}"></div>
                        @endforeach
                    </div>
                    <div class="builder-actions"><button type="reset"
                            class="builder-button builder-button-secondary">{{ __('pages.cancel') }}</button><button
                            type="submit" class="builder-button"><x-admin::icon
                                name="save" />{{ __('pages.save') }}</button></div>

                </form>
            </section>
        </div>

        <dialog class="builder-dialog builder-preview" data-preview-dialog><button type="button"
                class="builder-button builder-button-secondary" data-close-preview>{{ __('pages.close') }}</button>
            <p class="builder-preview-loading" data-preview-loading role="status" hidden>
                {{ __('pages.preview_loading') }}</p>
            <div class="builder-preview-content" data-preview-content data-title="{{ __('pages.preview') }}"></div>
        </dialog>
    </div>
@endsection
