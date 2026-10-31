<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F5F7FA">
    <meta name="description" content="J-ZAKAT — Sistem Pengurusan Kelayakan Asnaf">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'J-ZAKAT') — Sistem Pengurusan Kelayakan Asnaf</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg text-text font-sans antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
