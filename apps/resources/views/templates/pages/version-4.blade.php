@extends('templates.pages.layout')
@section('page_template_theme', ' page-template-version-4 page-template-invitation')
@section('page_template_content')
    @include('templates.pages.partials.invitation-header')
    <div class="template-skeleton v2-wedding-layout" id="home" role="region" aria-label="{{ __('pages.demo_layout') }}">
        <section class="v2-hero">
            <div class="skeleton-block v2-hero-image" aria-hidden="true"></div>
            <div class="v2-hero-copy"><span class="skeleton-block v2-hero-eyebrow" aria-hidden="true"></span>
                <h1><span>{{ $groomName }}</span><span class="v2-name-and">&amp;</span><span>{{ $brideName }}</span>
                </h1>
                <div class="v2-heart-divider" aria-hidden="true">♡</div><span class="skeleton-block v2-hero-date"
                    aria-hidden="true"></span><span class="skeleton-block v2-button-placeholder" aria-hidden="true"></span>
            </div>
            <div class="v2-countdown" aria-hidden="true">
                @for ($unit = 0; $unit < 4; $unit++)
                    <div><span class="skeleton-block v2-countdown-number"></span><span
                            class="skeleton-block v2-line"></span></div>
                @endfor
            </div>
            <svg class="v2-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
                <path
                    d="M0 78 C180 88 290 25 460 33 C590 38 630 58 720 48 C820 36 870 23 1000 35 C1170 51 1280 88 1440 78 L1440 100 L0 100Z" />
            </svg>
        </section>
        <section class="v2-section v4-story" id="v2-story">
            <h2>{{ __('pages.story') }}</h2>
            <div class="v4-couple-grid" id="v2-couple">
                <article>
                    <h3>{{ $brideName }}</h3>@include('templates.pages.partials.demo-lines', ['count' => 3])
                </article>
                <div class="skeleton-block v4-couple-portrait" aria-hidden="true"></div>
                <article>
                    <h3>{{ $groomName }}</h3>@include('templates.pages.partials.demo-lines', ['count' => 3])
                </article>
            </div>
            <div class="v2-timeline" aria-hidden="true">
                @for ($milestone = 0; $milestone < 4; $milestone++)
                    <article><span class="skeleton-block v4-year"></span><span
                            class="v2-milestone">♡</span>@include('templates.pages.partials.demo-lines')</article>
                @endfor
            </div>
        </section>
        @include('templates.pages.partials.invitation-events')
        @include('templates.pages.partials.invitation-gallery')
        @include('templates.pages.partials.invitation-wishes')
        <section class="v2-section v4-thanks">
            <div class="v4-collage" aria-hidden="true">
                <div class="skeleton-block v4-thanks-photo"></div>
                @for ($photo = 0; $photo < 3; $photo++)
                    <span class="skeleton-block v4-small-photo"></span>
                @endfor
            </div>
            <div>
                <h2>{{ __('pages.demo_thanks') }}</h2>
                <h3>{{ $groomName }} &amp; {{ $brideName }}</h3>@include('templates.pages.partials.demo-lines', ['count' => 3])
            </div>
        </section>
    </div>
    @include('templates.pages.partials.invitation-footer')
@endsection
