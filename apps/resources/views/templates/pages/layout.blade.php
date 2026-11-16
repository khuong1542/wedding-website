<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'vn' ? 'vi' : 'en' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?: $pageTitle }}</title>
    <meta name="description" content="{{ $seoDescription ?: $summary }}">
    @vite('resources/css/templates/pages.css')
    @include('templates.pages.partials.appearance')
</head>

<body class="page-template @yield('page_template_theme')">
    <main>@yield('page_template_content')</main>
</body>

</html>
