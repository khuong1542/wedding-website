@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-story')
@section('page_template_content')
    <header class="template-story-header">
        @if ($image)
        <img class="template-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">@else<span
                class="template-ornament" aria-hidden="true">♡</span>
        @endif
        <h1>{{ $groomName }} &amp; {{ $brideName }}</h1>
    </header>
    @include('templates.pages.skeleton')
@endsection
