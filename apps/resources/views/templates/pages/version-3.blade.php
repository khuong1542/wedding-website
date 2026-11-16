@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-minimal page-template-version-3 page-template-invitation')
@section('page_template_content')
    @include('templates.pages.partials.invitation-header')
    <div class="template-skeleton v2-wedding-layout" id="home" role="region" aria-label="{{ __('pages.demo_layout') }}">
        <section class="v2-hero">
            <div class="v3-orbit" aria-hidden="true">
                @for ($photo = 0; $photo < 3; $photo++)
                    <div class="v3-polaroid"><span class="skeleton-block"></span></div>
                @endfor
            </div>
            <div class="v2-hero-copy"><span class="skeleton-block v2-hero-eyebrow" aria-hidden="true"></span>
                <h1><span>{{ $groomName }}</span><span class="v2-name-and">&amp;</span><span>{{ $brideName }}</span>
                </h1>
                <div class="v2-heart-divider" aria-hidden="true">♡</div><span class="skeleton-block v2-hero-date"
                    aria-hidden="true"></span><span class="skeleton-block v2-button-placeholder" aria-hidden="true"></span>
            </div>
            <svg class="v2-wave" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
                <path
                    d="M0 78 C180 88 290 25 460 33 C590 38 630 58 720 48 C820 36 870 23 1000 35 C1170 51 1280 88 1440 78 L1440 100 L0 100Z" />
            </svg>
        </section>
        @include('templates.pages.partials.invitation-welcome')
        @include('templates.pages.partials.invitation-couple')
        @include('templates.pages.partials.invitation-story')
        @include('templates.pages.partials.invitation-events')
        @include('templates.pages.partials.invitation-gallery')
        @include('templates.pages.partials.invitation-rsvp')
        @include('templates.pages.partials.invitation-wishes')
        @include('templates.pages.partials.invitation-gifts')
    </div>
    @include('templates.pages.partials.invitation-footer')
@endsection
