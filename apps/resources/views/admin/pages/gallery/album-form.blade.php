<div class="gallery-field"><label for="gallery-album-title">{{ __('gallery.title') }} *</label><input
        id="gallery-album-title" name="title" value="{{ old('title', $selectedAlbum?->title) }}" maxlength="120" required>
</div>
<div class="gallery-field"><label for="gallery-album-description">{{ __('gallery.description_field') }}</label>
    <textarea id="gallery-album-description" name="description" rows="4" maxlength="1000">{{ old('description', $selectedAlbum?->description) }}</textarea>
</div>
<div class="gallery-field"><label for="gallery-album-position">{{ __('gallery.position') }}</label><input
        id="gallery-album-position" name="position" type="number" min="0" max="100000"
        value="{{ old('position', $selectedAlbum?->position ?? 0) }}" required></div>
<div class="gallery-field"><input type="hidden" name="is_visible" value="0"><label class="gallery-switch-label"
        for="gallery-album-visible">{{ __('gallery.visible') }}<input class="gallery-switch" type="checkbox"
            name="is_visible" id="gallery-album-visible" value="1" role="switch"
            @checked(old('is_visible', $selectedAlbum?->is_visible ?? true))></label></div>
