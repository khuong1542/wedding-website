@extends('admin.layouts.index')

@push('styles')
    @vite('resources/css/admin/pages/our-story.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/pages/our-story.js')
@endpush

@section('description', __('story.description'))

@section('actions')
    <button class="story-button" type="button" data-add-milestone><x-admin::icon
            name="plus" />{{ __('story.add') }}</button>
@endsection

@section('content')
    <form id="story-form" class="story-form" method="POST"
        action="{{ route('admin.our-story.update', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data"
        data-story-form data-add-label="{{ __('story.add') }}" data-options-label="{{ __('story.options') }}"
        data-edit-label="{{ __('story.edit') }}" data-delete-label="{{ __('story.remove') }}"
        data-order-url="{{ route('admin.our-story.order') }}" data-order-saved="{{ __('story.order_saved') }}"
        data-order-error="{{ __('story.order_error') }}" data-order-unsaved="{{ __('story.order_unsaved') }}"
        data-drag-label="{{ __('story.drag') }}" data-untitled="{{ __('story.untitled') }}"
        data-no-date="{{ __('story.no_date') }}" data-visible-label="{{ __('story.visible') }}"
        data-photo-error="{{ __('story.photo_error') }}" data-limit="{{ __('story.limit') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="title" value="{{ $story?->title ?: __('admin.our_story') }}">
        <div class="story-workspace">
            <aside class="story-list-panel admin-panel">
                <div class="story-list-heading">
                    <h2><x-admin::icon name="list-ul" />{{ __('story.list') }}</h2><span data-milestone-count>0</span>
                </div>
                <div class="story-cards" data-story-cards></div>
                <div class="story-empty" data-story-empty><x-admin::icon name="book-heart" />
                    <p>{{ __('story.empty') }}</p>
                </div>
            </aside>
            <section class="story-timeline">
                <div data-milestone-list>
                    @foreach (old('milestones', session()->hasOldInput('title') ? [] : $story?->milestones->toArray() ?? []) as $key => $item)
                        @include('admin.pages.our-story.milestone', [
                            'key' => $key,
                            'item' => $item,
                            'photo' =>
                                !empty($item['id']) && $story?->milestones->firstWhere('id', $item['id'])?->photo
                                    ? route('admin.our-story.photo', $item['id'])
                                    : null,
                        ])
                    @endforeach
                </div>
                <div class="story-editor-empty admin-panel" data-editor-empty><x-admin::icon name="edit" />
                    <p>{{ __('story.select') }}</p>
                </div>
            </section>
        </div>
        <div class="story-save-bar"><button class="story-button story-button-secondary"
                type="reset">{{ __('story.reset') }}</button><button class="story-button" type="submit"><x-admin::icon
                    name="save" />{{ __('story.save') }}</button></div>
        <dialog class="story-confirm" data-delete-dialog aria-labelledby="story-delete-title">
            <h2 id="story-delete-title">{{ __('story.confirm_title') }}</h2>
            <p>{{ __('story.confirm_delete') }}</p>
            <strong data-delete-name></strong>
            <div class="story-actions"><button type="button" class="story-button story-button-secondary"
                    data-cancel-delete>{{ __('story.cancel') }}</button><button type="button"
                    class="story-button story-button-danger" data-confirm-delete>{{ __('story.remove') }}</button></div>
        </dialog>
        <template data-milestone-template>@include('admin.pages.our-story.milestone', ['key' => '__KEY__', 'item' => [], 'photo' => null])</template>
    </form>
@endsection
