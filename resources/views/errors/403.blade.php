<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('errors.forbidden.code') }} — {{ __('errors.forbidden.title') }} | J-ZAKAT</title>
    @vite(['resources/css/app.css'])
    <style>html{font-family:'Inter','Segoe UI',system-ui,sans-serif}</style>
</head>
<body class="min-h-screen bg-bg text-text antialiased flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-[12px] bg-danger-soft mx-auto flex items-center justify-center mb-6 ring-1 ring-danger/10">
            <span class="text-metric-xl text-danger tabular-nums">{{ __('errors.forbidden.code') }}</span>
        </div>
        <h1 class="text-title text-text mb-3">{{ __('errors.forbidden.title') }}</h1>
        <p class="text-body text-secondary mb-8">{{ __('errors.forbidden.message') }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary btn-md">{{ __('errors.forbidden.back') }}</a>
    </div>
</body>
</html>
