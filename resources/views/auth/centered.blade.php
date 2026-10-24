<!DOCTYPE html>
<html lang="ms" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <meta name="description" content="J-ZAKAT AI — J-ZAKAT MAIJ Sistem Bersepadu Majlis Agama Islam Negeri Johor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'J-ZAKAT MAIJ — J-ZAKAT AI')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=amiri:400,700&display=swap" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="emaij-body min-h-screen antialiased">
    @include('auth._emaij-background')

    <main class="emaij-page">
        <div class="emaij-page__inner @yield('inner-class')">
            <div class="emaij-page__brand">
                @include('auth._emaij-brand')
            </div>

            <div class="emaij-page__content">
                @yield('content')
            </div>

            <div class="emaij-page__footer">
                @yield('footer')
                <p class="emaij-page__copyright">&copy; Hak Milik Akmal Hafiz 2020 - {{ date('Y') }}</p>
            </div>
        </div>
    </main>

    @stack('scripts')
</body>
</html>
