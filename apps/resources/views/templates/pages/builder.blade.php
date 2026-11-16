@extends('templates.pages.layout')
@section('page_template_theme', 'page-template-invitation page-template-builder')
@section('page_template_content')
    @include('templates.pages.partials.site-header')
    <div class="builder-published-content" id="home">
        @foreach ($blocks as $block)
            <section id="block-{{ $block['id'] }}"
                class="builder-published-block {{ !$block['desktop'] ? 'builder-hide-desktop' : '' }} {{ !$block['tablet'] ? 'builder-hide-tablet' : '' }} {{ !$block['mobile'] ? 'builder-hide-mobile' : '' }} {{ $block['animation'] === 'fade' ? 'builder-fade' : '' }}"
                style="text-align: {{ $block['align'] }}; background: {{ $block['background'] }}; padding: {{ (int) $block['padding'] }}px; min-height: {{ (int) $block['height'] }}px; --builder-duration: {{ (int) $block['duration'] }}ms"
                aria-label="{{ $block['name'] }}">
                @switch($block['type'])
                    @case('hero')
                        @if ($block['url'])
                            <img class="builder-content-image" src="{{ $block['url'] }}" alt="{{ $block['title'] }}">
                        @endif
                        <h1>{{ $block['title'] ?: $groomName . ' & ' . $brideName }}</h1>
                        <p>{{ $block['text'] }}</p>
                    @break

                    @case('heading')
                        <h2>{{ $block['title'] }}</h2>
                    @break

                    @case('text')
                        <p>{{ $block['text'] }}</p>
                    @break

                    @case('image')
                        @if ($block['url'])
                            <img class="builder-content-image" src="{{ $block['url'] }}" alt="{{ $block['title'] }}">
                        @endif
                    @break

                    @case('button')
                        @if ($block['url'])
                            <a class="builder-content-button" href="{{ $block['url'] }}"
                                rel="noopener noreferrer">{{ $block['button_label'] }}</a>
                        @endif
                    @break

                    @case('divider')
                        <hr>
                    @break

                    @case('quote')
                        <blockquote>{{ $block['text'] }}</blockquote>
                    @break
                @endswitch
            </section>
        @endforeach
    </div>
    @include('templates.pages.partials.invitation-footer')
@endsection
