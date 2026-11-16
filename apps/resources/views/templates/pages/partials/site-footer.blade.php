@if ($websiteNavigation['display']['show_footer'])
    <footer class="v2-footer website-footer">
        <div class="website-footer-grid">
            <div class="website-footer-about">
                @if ($footerLogo ?? null)
                    <img class="website-footer-logo" src="{{ $footerLogo }}"
                        alt="{{ $websiteNavigation['footer']['brand'] }}">
                @endif
                <strong>{{ $websiteNavigation['footer']['brand'] }}</strong>
                <p>{{ $websiteNavigation['footer']['description'] }}</p>
                <div class="website-socials">
                    @foreach (['facebook', 'instagram', 'youtube', 'tiktok'] as $social)
                        @if ($websiteNavigation['footer'][$social])
                            <a href="{{ $websiteNavigation['footer'][$social] }}" target="_blank"
                                rel="noopener noreferrer">{{ ucfirst($social) }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div>
                <h3>{{ __('navigation.quick_links') }}</h3>
                <nav aria-label="{{ __('navigation.quick_links') }}">
                    @foreach ($websiteNavigation['footer_links'] as $link)
                        @if ($link['visible'])
                            <a href="{{ $link['url'] }}"
                                @if ($websiteNavigation['display']['new_tab']) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
                        @endif
                    @endforeach
                </nav>
            </div>
            <div>
                <h3>{{ __('navigation.contact') }}</h3>
                @if ($websiteNavigation['footer']['email'])
                    <a
                        href="mailto:{{ $websiteNavigation['footer']['email'] }}">{{ $websiteNavigation['footer']['email'] }}</a>
                    @endif @if ($websiteNavigation['footer']['phone'])
                        <a
                            href="tel:{{ preg_replace('/[^0-9+]/', '', $websiteNavigation['footer']['phone']) }}">{{ $websiteNavigation['footer']['phone'] }}</a>
                        @endif @if ($websiteNavigation['footer']['address'])
                            <p>{{ $websiteNavigation['footer']['address'] }}</p>
                        @endif
            </div>
        </div>
        <div class="website-copyright">{{ $websiteNavigation['footer']['copyright'] }}</div>
    </footer>
@endif
