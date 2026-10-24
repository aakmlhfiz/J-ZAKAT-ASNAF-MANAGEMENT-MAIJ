@php
    $isPortal = auth('portal')->check();
    $searchAction = $isPortal ? route('user.applications.index') : route('viewprofile.index');
    $searchPlaceholder = $isPortal ? 'Cari permohonan…' : 'Cari pemohon…';
@endphp
<header class="sticky top-0 z-30 bg-card/85 backdrop-blur-xl border-b border-divider shadow-[0_1px_0_rgba(15,23,42,0.04)]" x-data="{ searchFocus: false }">
    <div class="px-5 md:px-8 py-4 flex items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2.5">
                <button @click="$dispatch('toggle-sidebar')" class="md:hidden w-9 h-9 rounded-[11px] hover:bg-muted flex items-center justify-center text-secondary hover:text-text transition-fast shrink-0" aria-label="Toggle sidebar">
                    <x-icon name="menu" :size="19" />
                </button>
                <div class="min-w-0 flex-1">
                    <x-breadcrumbs />
                    @if(($title ?? null) && ($title ?? '') !== '')
                        <h1 class="text-[20px] sm:text-[23px] font-semibold text-text truncate tracking-tight">{{ $title }}</h1>
                    @endif
                    @if($subtitle ?? null)
                        <p class="text-small text-secondary mt-0.5 truncate hidden sm:block">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button @click="cycleDarkMode()" class="w-10 h-10 rounded-[12px] hover:bg-muted flex items-center justify-center text-secondary hover:text-text transition-fast" aria-label="Tukar tema" title="Tukar tema">
                <x-icon x-show="!dark" name="moon" :size="18" />
                <x-icon x-show="dark" x-cloak name="sun" :size="18" />
            </button>

            <form method="GET" action="{{ $searchAction }}" class="relative hidden lg:block" @focusin="searchFocus = true" @focusout="searchFocus = false">
                <x-icon name="search" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
                <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}" class="topbar-search">
                <kbd class="kbd absolute right-2 top-1/2 -translate-y-1/2 hidden xl:flex" :class="searchFocus ? 'opacity-0' : 'opacity-100'" style="transition: opacity 150ms;">⌘K</kbd>
            </form>

            @isset($actions){{ $actions }}@endisset
            <x-notifications-bell />
            <x-user-menu />
        </div>
    </div>
</header>
