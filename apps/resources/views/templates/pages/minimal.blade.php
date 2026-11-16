@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-minimal')
@section('page_template_content')
    @if ($image)
        <img class="template-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">
    @endif
    <h1>{{ $groomName }} &amp; {{ $brideName }}</h1>
    <div class="template-rule"></div>
    @include('templates.pages.skeleton')
@endsection
