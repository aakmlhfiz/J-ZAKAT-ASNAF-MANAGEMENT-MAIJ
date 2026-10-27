<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="J-ZAKAT — {{ __('auth.brand.tagline') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'J-ZAKAT')</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
<div class="auth-shell auth-shell--centered">
    <main class="auth-panel auth-panel--centered">
        @include('auth._auth-topbar')

        <div class="auth-panel__content">
            <div class="auth-card">
                @yield('content')

                @hasSection('footer')
                    <div class="auth-card__footer">
                        @yield('footer')
                    </div>
                @endif
            </div>

            @include('auth._auth-footer')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
