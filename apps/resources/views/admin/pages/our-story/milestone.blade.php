<article class="story-milestone" data-milestone data-key="{{ $key }}">
    <input type="hidden" name="milestones[{{ $key }}][id]" value="{{ $item['id'] ?? '' }}">
    <div class="story-milestone-heading">
        <h3><span data-milestone-number></span><span data-editor-title>{{ __('story.edit') }}</span></h3>
        <div class="story-row-actions">
            <button type="button" data-move-up aria-label="{{ __('story.up') }}"
                title="{{ __('story.up') }}"><x-admin::icon name="chevron-up" /></button>
            <button type="button" data-move-down aria-label="{{ __('story.down') }}"
                title="{{ __('story.down') }}"><x-admin::icon name="chevron-down" /></button>
            <button type="button" data-remove-milestone aria-label="{{ __('story.remove') }}"
                title="{{ __('story.remove') }}"><x-admin::icon name="trash" /></button>
        </div>
    </div>
    <div class="story-milestone-body">
        <div class="story-image-picker">
            <label class="story-image-frame" for="moment-{{ $key }}-photo">
                <span data-photo-hint @if ($photo && empty($item['remove_photo'])) hidden @endif><x-admin::icon
                        name="camera-plus" /><span>{{ __('story.photo_help') }}</span></span>
                <img data-story-image alt="{{ __('story.photo') }}"
                    @if ($photo && empty($item['remove_photo'])) src="{{ $photo }}" @else hidden @endif>
                <input id="moment-{{ $key }}-photo" type="file"
                    name="milestones[{{ $key }}][photo]" accept="image/jpeg,image/png,image/webp"
                    data-story-photo aria-label="{{ __('story.photo') }}">
            </label>
            <input type="hidden" name="milestones[{{ $key }}][remove_photo]"
                value="{{ $item['remove_photo'] ?? '0' }}" data-remove-photo-value>
            <button type="button" class="story-remove-photo" data-remove-photo
                aria-label="{{ __('story.remove_photo') }}"
                @if (!$photo || !empty($item['remove_photo'])) hidden @endif><x-admin::icon name="x" /></button>
        </div>
        <div class="story-milestone-fields">
            <div class="story-field"><label for="moment-{{ $key }}-title">{{ __('story.moment_title') }}
                    <span>*</span></label><input id="moment-{{ $key }}-title"
                    name="milestones[{{ $key }}][title]" value="{{ $item['title'] ?? '' }}" maxlength="120"
                    required></div>
            <div class="story-field"><label for="moment-{{ $key }}-date">{{ __('story.date') }}</label><input
                    id="moment-{{ $key }}-date" type="date" name="milestones[{{ $key }}][date]"
                    value="{{ $item['date'] ?? '' }}"></div>
            <div class="story-field story-visibility"><input type="hidden"
                    name="milestones[{{ $key }}][is_visible]" value="0"><label
                    class="story-toggle-label"><input type="checkbox"
                        name="milestones[{{ $key }}][is_visible]" value="1" data-story-visible
                        @checked($item['is_visible'] ?? true)><span class="story-toggle"
                        aria-hidden="true"></span>{{ __('story.visible') }}</label></div>
        </div>
        <div class="story-field story-field-wide"><label
                for="moment-{{ $key }}-description">{{ __('story.description_field') }}</label>
            <textarea id="moment-{{ $key }}-description" name="milestones[{{ $key }}][description]"
                rows="7" maxlength="3000" data-story-description>{{ $item['description'] ?? '' }}</textarea>
            <span class="story-character-count" data-character-count></span>
        </div>
        <div class="story-field story-order"><label
                for="moment-{{ $key }}-position">{{ __('story.order') }}</label><input
                id="moment-{{ $key }}-position" type="number" min="1" value="1"
                data-story-position></div>
    </div>
</article>
