@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-classic')
@section('page_template_content')
    @if ($image)
    <img class="template-logo" src="{{ $image }}" alt="{{ __('pages.logo') }}">@else<div class="template-ornament"
            aria-hidden="true">♡</div>
    @endif
    <h1>{{ $pageTitle }}</h1>
    @include('templates.pages.skeleton')
@endsection
