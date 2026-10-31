<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="darkMode" :class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F5F7FA">
    <meta name="description" content="J-ZAKAT — {{ __('navigation.brand.portal') }} · Majlis Agama Islam Johor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-role" content="user">
    <meta name="user-id" content="{{ auth('portal')->id() ?? '' }}">
    <meta name="user-guard" content="portal">
    <meta name="user-dark-mode" content="{{ auth('portal')->user()?->dark_mode === 'dark' ? 'dark' : 'light' }}">
    <title>@yield('title', __('navigation.brand.portal')) — J-ZAKAT</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            // Light-first institutional theme. Dark mode is an explicit
            // opt-in from the portal profile settings.
            var userMode = document.querySelector('meta[name="user-dark-mode"]')?.content || 'light';
            if (userMode === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="h-full bg-bg text-text antialiased min-h-screen">
    <div class="flex min-h-screen" x-data="appSidebar" @toggle-sidebar.window="toggle()">
        <x-app-sidebar />
        <div class="flex-1 min-w-0 flex flex-col">
            <x-app-topbar :title="$title ?? ''" :subtitle="$subtitle ?? null">
                @isset($topbarActions) <x-slot:actions>{{ $topbarActions }}</x-slot:actions> @endisset
            </x-app-topbar>
            <main class="flex-1 px-5 md:px-8 py-6 md:py-8 page-transition bg-bg">
                @if(session('success'))
                    <div class="surface p-4 mb-6 flex items-center gap-3 animate-fade-in" style="background-color: var(--color-success-soft);">
                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(52, 199, 89, 0.15); color: var(--color-success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <p class="text-body text-text font-medium flex-1">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="surface p-4 mb-6 flex items-center gap-3 animate-fade-in" style="background-color: var(--color-danger-soft);">
                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(255, 59, 48, 0.15); color: var(--color-danger);">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                        <p class="text-body text-text font-medium flex-1">{{ session('error') }}</p>
                    </div>
                @endif
                @yield('content')
            </main>
            <footer class="px-6 md:px-10 py-5 text-center text-small text-secondary border-t border-divider bg-card/40">
                <p>&copy; {{ date('Y') }} <span class="font-semibold text-text">J-ZAKAT</span> · {{ __('common.footer.platform') }}</p>
                <p class="text-[11px] mt-1 text-secondary">{{ __('common.footer.developed_for') }}</p>
            </footer>
        </div>
    </div>
    <x-confirm-dialog />
    @stack('scripts')
</body>
</html>
