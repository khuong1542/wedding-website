<header
    class="v2-header website-header website-header-{{ $websiteNavigation['display']['layout'] }} website-logo-{{ $websiteNavigation['display']['logo_position'] }}">
    <a class="v2-brand" href="#home">
        @if ($image ?? null)
        <img class="v2-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">@else<span class="v2-brand-heart"
                aria-hidden="true">♡</span>
        @endif
        <span>{{ $pageTitle ?? $websiteNavigation['footer']['brand'] }}</span>
    </a>
    <nav class="v2-nav website-menu" aria-label="{{ __('navigation.menu') }}">
        @foreach ($websiteNavigation['menu'] as $item)
            @if ($item['visible'])
                <a href="{{ $item['url'] }}"
                    @if ($websiteNavigation['display']['new_tab']) target="_blank" rel="noopener noreferrer" @endif>
                    @if ($websiteNavigation['display']['show_icons'])
                        <x-admin::icon :name="$item['icon']" />
                    @endif{{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</header>
