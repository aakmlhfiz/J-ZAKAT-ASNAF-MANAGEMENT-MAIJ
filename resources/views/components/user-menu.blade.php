@php
    $isPortal = auth('portal')->check();
    $user = $isPortal ? auth('portal')->user() : auth('web')->user();
    $name = $user?->name ?? ($isPortal ? __('navigation.role.applicant') : __('navigation.role.administrator'));
    $email = $user?->email ?? ($isPortal ? 'pemohon@maij.gov.my' : 'admin@maij.gov.my');
    $initials = strtoupper(mb_substr($name, 0, 1));
    $photo = $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null;

    $profileRoute = $isPortal ? route('user.profile') : route('profile');
    $logoutRoute = $isPortal ? route('user.logout') : route('logout');
    $statusLabel = $isPortal ? __('navigation.user_menu.status_portal') : __('navigation.user_menu.status_admin');
@endphp
<div x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     @close-confirm.window="if ($event.detail?.action === 'logout') { document.getElementById('logout-form').submit(); }"
     class="relative">
    <form id="logout-form" method="POST" action="{{ $logoutRoute }}" class="hidden">@csrf</form>
    <button @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="menu"
            class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-[8px] hover:bg-muted transition-fast border border-transparent hover:border-divider">
        <span class="w-8 h-8 rounded-full text-white flex items-center justify-center text-[12px] font-semibold ring-2 ring-card overflow-hidden shrink-0" style="background: var(--gradient-royal);">
            @if($photo)
                <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-full object-cover">
            @else
                {{ $initials }}
            @endif
        </span>
        <span class="hidden md:inline text-small text-text font-medium pr-1 truncate max-w-[120px]">{{ $name }}</span>
        <x-icon name="chevron-down" :size="13" class="text-tertiary transition-fast" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-72 surface-elevated z-50 overflow-hidden">
        <div class="px-4 py-4 border-b border-divider" style="background: var(--color-primary-soft);">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-full text-white flex items-center justify-center text-[14px] font-semibold ring-2 ring-card overflow-hidden shrink-0" style="background: var(--gradient-royal);">
                    @if($photo)
                        <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-body text-text font-semibold truncate">{{ $name }}</p>
                    <p class="text-small text-secondary truncate">{{ $email }}</p>
                </div>
            </div>
            <span class="mt-2.5 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-success-soft text-success">
                <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                {{ $statusLabel }}
            </span>
        </div>
        <div class="p-2">
            <a href="{{ $profileRoute }}" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-body text-text hover:bg-muted transition-fast">
                <x-icon name="user" :size="17" class="text-tertiary" />
                <span>{{ __('navigation.user_menu.profile') }}</span>
            </a>
            @if($isPortal)
                <a href="{{ route('user.hadkifayah') }}" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-body text-text hover:bg-muted transition-fast">
                    <x-icon name="calculator" :size="17" class="text-tertiary" />
                    <span>{{ __('navigation.user_menu.had_kifayah') }}</span>
                </a>
            @else
                <a href="{{ route('settings') }}" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-body text-text hover:bg-muted transition-fast">
                    <x-icon name="settings" :size="17" class="text-tertiary" />
                    <span>{{ __('navigation.user_menu.settings') }}</span>
                </a>
                <a href="{{ route('hadkifayah.settings') }}" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-body text-text hover:bg-muted transition-fast">
                    <x-icon name="sliders" :size="17" class="text-tertiary" />
                    <span>{{ __('navigation.user_menu.had_kifayah') }}</span>
                </a>
            @endif
        </div>
        <div class="px-3 py-2.5 border-t border-divider flex items-center justify-between gap-3">
            <span class="text-small text-secondary">{{ __('common.language') }}</span>
            <x-language-switcher variant="light" />
        </div>
        <div class="p-2 border-t border-divider">
            <button @click="$dispatch('open-confirm', { title: '{{ __('navigation.user_menu.logout_title') }}', message: '{{ __('navigation.user_menu.logout_message') }}', confirmText: '{{ __('navigation.user_menu.logout') }}', variant: 'destructive', action: 'logout' }); open = false;"
                    role="menuitem"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-[8px] text-body text-danger hover:bg-danger-soft transition-fast">
                <x-icon name="log-out" :size="17" />
                <span>{{ __('navigation.user_menu.logout') }}</span>
            </button>
        </div>
    </div>
</div>
