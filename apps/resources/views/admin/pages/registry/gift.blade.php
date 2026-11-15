<div class="registry-gift" data-gift>
    <div class="registry-field"><label for="gift-{{ $key }}-title">{{ __('registry.title') }} *</label><input
            id="gift-{{ $key }}-title" name="gifts[{{ $key }}][title]" maxlength="120" required
            value="{{ $gift['title'] ?? '' }}"></div>
    <div class="registry-field"><label for="gift-{{ $key }}-url">{{ __('registry.url') }}</label><input
            id="gift-{{ $key }}-url" name="gifts[{{ $key }}][url]" type="url" maxlength="1000"
            placeholder="https://" value="{{ $gift['url'] ?? '' }}"></div>
    <div class="registry-field"><label for="gift-{{ $key }}-note">{{ __('registry.note') }}</label><input
            id="gift-{{ $key }}-note" name="gifts[{{ $key }}][note]" maxlength="1000"
            value="{{ $gift['note'] ?? '' }}"></div>
    <button type="button" data-remove-gift class="registry-icon-button"
        aria-label="{{ __('registry.remove') }}"><x-admin::icon name="trash" /></button>
</div>
