@if ($selected)
    <input type="hidden" name="type" value="{{ old('type', $selected->type) }}" data-gallery-type>
@else
    <div class="gallery-field">
        <label for="gallery-type">{{ __('gallery.type') }}</label><select id="gallery-type" name="type"
            data-gallery-type>
            @foreach (['image', 'video'] as $type)
                <option value="{{ $type }}" @selected(old('type', $selected?->type ?? (($filters['tab'] ?? '') === 'video' ? 'video' : 'image')) === $type)>{{ __('gallery.' . $type) }}</option>
            @endforeach
        </select>
    </div>
@endif
<div class="gallery-field" data-gallery-image-field>
    <div class="gallery-photo-picker"><label class="gallery-photo-frame" for="gallery-photo"><span
                data-gallery-photo-hint><x-admin::icon name="camera-plus" />{{ __('gallery.photo_hint') }}</span><img
                data-gallery-photo-image alt="{{ __('gallery.photo') }}"
                @if ($selected?->photo) src="{{ route('admin.gallery.photo', $selected) }}" @else hidden @endif><input
                id="gallery-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp"><span
                class="gallery-photo-change" data-gallery-photo-change><x-admin::icon
                    name="camera" />{{ __('gallery.change') }}</span></label></div>
</div>
<div class="gallery-field" data-gallery-video-field><label for="gallery-video-url">{{ __('gallery.video_url') }}
        *</label><input id="gallery-video-url" name="video_url" type="url" maxlength="1000"
        value="{{ old('video_url', $selected?->video_url) }}"
        placeholder="https://"><small>{{ __('gallery.video_help') }}</small></div>
<div class="gallery-field"><label for="gallery-title">{{ __('gallery.title') }} *</label><input id="gallery-title"
        name="title" value="{{ old('title', $selected?->title) }}" maxlength="120" required></div>
<div class="gallery-field"><label for="gallery-description">{{ __('gallery.description_field') }}</label>
    <textarea id="gallery-description" name="description" maxlength="3000" rows="2">{{ old('description', $selected?->description) }}</textarea>
</div>
<div class="gallery-field"><label for="gallery-album">{{ __('gallery.album') }}</label><select name="album_id"
        id="gallery-album">
        <option value="">{{ __('gallery.no_album') }}</option>
        @foreach ($albums as $album)
            <option value="{{ $album->id }}" @selected((int) old('album_id', $selected?->album_id ?? ($filters['album'] ?? 0)) === $album->id)>{{ $album->title }}</option>
        @endforeach
    </select></div>
<input type="hidden" name="alt_text" value="{{ old('alt_text', $selected?->alt_text) }}">
<div class="gallery-field"><label for="gallery-position">{{ __('gallery.position') }}</label><input
        id="gallery-position" name="position" type="number" min="0" max="100000"
        value="{{ old('position', $selected?->position ?? 0) }}" required></div>
<div class="gallery-field"><input type="hidden" name="is_visible" value="0"><label class="gallery-switch-label"
        for="gallery-visible">{{ __('gallery.visible') }}<input class="gallery-switch" type="checkbox"
            id="gallery-visible" name="is_visible" value="1" role="switch" @checked(old('is_visible', $selected?->is_visible ?? true))></label>
</div>
