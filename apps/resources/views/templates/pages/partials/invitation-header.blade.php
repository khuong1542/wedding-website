    <header class="v2-header">
        <a class="v2-brand" href="#home">
            @if ($image)
            <img class="v2-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">@else<span
                    class="v2-brand-heart" aria-hidden="true">♡♡</span>
            @endif
            <span>
                {{ $pageTitle }}</span>
        </a>
        <nav class="v2-nav" aria-label="{{ __('pages.demo_layout') }}">
            @foreach (['story', 'couple', 'events', 'gallery'] as $section)
                <a href="#v2-{{ $section }}">{{ __('pages.' . $section) }}</a>
            @endforeach
        </nav>
        <span class="skeleton-block v2-header-note" aria-hidden="true"></span>
    </header>
