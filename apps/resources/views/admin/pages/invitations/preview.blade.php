<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'vn' ? 'vi' : 'en' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invitation->seo_title ?: $invitation->title }}</title>
    <meta name="description" content="{{ $invitation->seo_description ?: $invitation->description }}">@vite('resources/css/admin/pages/invitations.css')
</head>

<body class="iv-preview-page">
    <main>@include('admin.pages.invitations.art')@if ($invitation->message)
            <p class="iv-message">{{ $invitation->message }}</p>
        @endif
    </main>
</body>

</html>
