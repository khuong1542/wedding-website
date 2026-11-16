@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-classic page-template-version-2')
@section('page_template_content')
    <header class="v2-header">
        <a class="v2-brand" href="#home">
            @if ($image)
            <img class="v2-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">@else<span class="v2-brand-heart"
                    aria-hidden="true">♡♡</span>
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
    <div class="v2-wedding-layout template-skeleton" id="home" role="region"
        aria-label="{{ __('pages.demo_layout') }}">
        <div class="v2-left-column">
            <section class="v2-hero">
                <div class="skeleton-block v2-hero-image" aria-hidden="true"></div>
                <div class="v2-hero-copy"><span class="skeleton-block v2-hero-eyebrow" aria-hidden="true"></span>
                    <h1><span>{{ $groomName }}</span><span
                            class="v2-name-and">&amp;</span><span>{{ $brideName }}</span></h1>
                    <div class="v2-heart-divider" aria-hidden="true">♡</div><span class="skeleton-block v2-hero-date"
                        aria-hidden="true"></span><span class="skeleton-block v2-button-placeholder"
                        aria-hidden="true"></span>
                </div>
                <svg class="v2-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
                    <path
                        d="M0 78 C180 88 290 25 460 33 C590 38 630 58 720 48 C820 36 870 23 1000 35 C1170 51 1280 88 1440 78 L1440 100 L0 100Z" />
                </svg>
            </section>
            <section class="v2-welcome v2-section">
                <h2>{{ __('pages.version2_welcome') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 2])<div class="v2-countdown"
                    aria-hidden="true">
                    @for ($unit = 0; $unit < 4; $unit++)
                        <div><span class="skeleton-block v2-countdown-number"></span><span
                                class="skeleton-block v2-line"></span></div>
                    @endfor
                </div>
            </section>
            <section class="v2-couple v2-section" id="v2-couple">
                <h2>{{ __('pages.couple') }}</h2>
                <div class="v2-couple-grid">
                    @foreach (['groom' => $groomName, 'bride' => $brideName] as $person => $name)
                        <article class="v2-person">
                            <div class="skeleton-block v2-portrait" aria-hidden="true"></div>
                            <div>
                                <h3>{{ $name }}</h3>@include('templates.pages.partials.demo-lines', ['count' => 2])
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
            <section class="v2-story v2-section" id="v2-story">
                <h2>{{ __('pages.story') }}</h2>
                <div class="v2-timeline" aria-hidden="true">
                    @for ($milestone = 0; $milestone < 4; $milestone++)
                        <article><span class="v2-milestone">♡</span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                    @endfor
                </div>
            </section>
        </div>
        <div class="v2-right-column">
            <section class="v2-events v2-section" id="v2-events">
                <h2>{{ __('pages.events') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 1])<div class="v2-event-grid" aria-hidden="true">
                    @for ($event = 0; $event < 3; $event++)
                        <article><span class="skeleton-block v2-event-image"></span>@include('templates.pages.partials.demo-lines', ['count' => 3])<span
                                class="skeleton-block v2-button-placeholder"></span></article>
                    @endfor
                </div>
            </section>
            <section class="v2-gallery v2-section" id="v2-gallery">
                <h2>{{ __('pages.gallery') }}</h2>
                <div class="v2-gallery-filters" aria-hidden="true">
                    @for ($filter = 0; $filter < 4; $filter++)
                        <span class="skeleton-block"></span>
                    @endfor
                </div>
                <div class="v2-gallery-grid" aria-hidden="true">
                    @for ($photo = 0; $photo < 6; $photo++)
                        <span class="skeleton-block"></span>
                    @endfor
                </div>
            </section>
            <section class="v2-rsvp v2-section">
                <h2>{{ __('pages.version2_rsvp') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 1])<div class="v2-demo-form"
                    aria-hidden="true">
                    @for ($field = 0; $field < 3; $field++)
                        <div><span class="skeleton-block v2-form-label"></span><span
                                class="skeleton-block v2-form-input"></span></div>
                    @endfor
                    <span class="skeleton-block v2-form-textarea">
                    </span><span class="skeleton-block v2-button-placeholder"></span>
                </div>
            </section>
            <section class="v2-wishes v2-section">
                <h2>{{ __('pages.wishes') }}</h2>
                <div class="v2-wishes-layout" aria-hidden="true">
                    <div class="v2-wish-list">
                        @for ($wish = 0; $wish < 3; $wish++)
                            <article><span class="skeleton-block v2-avatar"></span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                        @endfor
                    </div>
                    <div class="v2-demo-form">
                        @for ($input = 0; $input < 2; $input++)
                            <span class="skeleton-block v2-form-label"></span><span
                                class="skeleton-block v2-form-input"></span>
                        @endfor
                        <span class="skeleton-block v2-form-textarea">
                        </span><span class="skeleton-block v2-button-placeholder"></span>
                    </div>
                </div>
            </section>
            <section class="v2-gifts v2-section">
                <h2>{{ __('pages.registry') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 2])<div class="v2-gift-grid"
                    aria-hidden="true">
                    @for ($gift = 0; $gift < 2; $gift++)
                        <article><span class="skeleton-block v2-qr"></span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                    @endfor
                </div>
            </section>
        </div>
    </div>
    <footer class="v2-footer"><strong>{{ $groomName }} &amp; {{ $brideName }}</strong>
        <div class="v2-footer-links" aria-hidden="true">
            @for ($link = 0; $link < 5; $link++)
                <span class="skeleton-block"></span>
            @endfor
        </div><span class="skeleton-block v2-footer-note" aria-hidden="true"></span>
    </footer>
@endsection
