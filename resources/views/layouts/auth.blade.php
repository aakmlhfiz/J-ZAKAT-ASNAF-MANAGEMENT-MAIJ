<!DOCTYPE html>
<html lang="ms" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <meta name="description" content="J-ZAKAT — Smart Asnaf Eligibility System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'J-ZAKAT') — Smart Asnaf Eligibility</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg text-text font-sans antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
