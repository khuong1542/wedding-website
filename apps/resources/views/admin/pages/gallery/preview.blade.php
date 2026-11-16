<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'vn' ? 'vi' : 'en' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $media->title }}</title>@vite('resources/css/admin/pages/gallery.css')
</head>

<body class="gallery-viewer">
    <main>
        <h1>{{ $media->title }}</h1>
        @if ($media->type === 'image')
            <img src="{{ route('admin.gallery.photo', $media) }}" alt="{{ $media->alt_text ?: $media->title }}">
        @elseif ($embed)
            <iframe src="{{ $embed }}" title="{{ $media->title }}" allowfullscreen
                allow="fullscreen; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"></iframe>@else<video controls preload="metadata"
                src="{{ $media->video_url }}"></video>
            @endif @if ($media->description)
                <p>{{ $media->description }}</p>
                @endif @if ($media->type === 'video')
                    <a href="{{ $media->video_url }}" target="_blank"
                        rel="noopener noreferrer">{{ __('gallery.open_video') }}</a>
                @endif
    </main>
</body>

</html>
