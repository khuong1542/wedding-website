<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'vn' ? 'vi' : 'en' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('sidebar.name') }}</title>
    <script src="{{ Vite::asset('resources/js/admin/preferences.js') }}"></script>
    @vite(['resources/css/admin/app.css', 'resources/js/admin/app.js'])
    @stack('styles')
    @stack('scripts')
</head>

<body class="admin-body">
    <a class="admin-skip" href="#main-content">{{ __('admin.skip') }}</a>
    <div class="admin-shell" data-admin>
        @include('admin.layouts.sidebar')
        @include('admin.layouts.header')
        <main class="admin-main" id="main-content" tabindex="-1">
            <div class="admin-page-heading">
                <div>
                    <p>{{ __('admin.workspace') }}</p>
                    <h1>{{ $title }}</h1>
                    <span>@yield('description', __('admin.dashboard_description'))</span>
                </div>
                @yield('actions')
            </div>
            @yield('content')
            <footer class="admin-page-footer">© {{ date('Y') }}
                {{ config('sidebar.name') }}<span>{{ __('admin.made_with_care') }}</span></footer>
        </main>
    </div>
    <x-admin::toasts />
</body>

</html>
