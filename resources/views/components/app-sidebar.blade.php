@php
    $isPortal = auth('portal')->check();
    $user = $isPortal ? auth('portal')->user() : auth('web')->user();
    $initials = $user ? strtoupper(mb_substr($user->name, 0, 1)) : ($isPortal ? 'P' : 'A');
    $photo = $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null;
    $isActive = fn($route) => request()->routeIs($route);

    if ($isPortal) {
        $singleItems = [
            ['route' => 'user.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ];
        $groups = [
            'permohonan' => ['label' => 'Permohonan', 'items' => [
                ['route' => 'user.applications.create', 'icon' => 'file-plus', 'label' => 'Mohon Bantuan'],
                ['route' => 'user.applications.index', 'icon' => 'file-text', 'label' => 'Permohonan Saya'],
                ['route' => 'user.applications.status', 'icon' => 'chart-line', 'label' => 'Status Permohonan'],
            ]],
            'keluarga' => ['label' => 'Keluarga', 'items' => [
                ['route' => 'user.profile', 'icon' => 'user', 'label' => 'Profil Saya'],
                ['route' => 'user.household', 'icon' => 'house', 'label' => 'Isi Rumah Saya'],
                ['route' => 'user.household.finance', 'icon' => 'wallet', 'label' => 'Kewangan Saya'],
                ['route' => 'user.household.assistance', 'icon' => 'hand-heart', 'label' => 'Bantuan Saya'],
            ]],
            'dokumen' => ['label' => 'Dokumen', 'items' => [
                ['route' => 'user.documents.index', 'icon' => 'folder', 'label' => 'Dokumen Saya'],
                ['route' => 'user.letters.index', 'icon' => 'mail', 'label' => 'Surat & Resit'],
            ]],
            'bantuan' => ['label' => 'Bantuan', 'items' => [
                ['route' => 'user.ai.page', 'icon' => 'bot', 'label' => 'AI Assistant'],
                ['route' => 'user.notifications.index', 'icon' => 'bell', 'label' => 'Notifikasi'],
                ['route' => 'user.hadkifayah', 'icon' => 'calculator', 'label' => 'Kira Had Kifayah'],
            ]],
            'akaun' => ['label' => 'Akaun', 'items' => [
                ['route' => 'user.profile', 'icon' => 'settings', 'label' => 'Tetapan'],
            ]],
        ];
        $isSuperAdmin = false;
    } else {
        $singleItems = [
            ['route' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ];
        $groups = [
            'household' => ['label' => 'Isi Rumah', 'items' => [
                ['route' => 'household.dashboard', 'icon' => 'house', 'label' => 'Household Intelligence'],
            ]],
            'asnaf' => ['label' => 'Asnaf', 'items' => [
                ['route' => 'profilasnaf', 'icon' => 'user-plus', 'label' => 'Profil Asnaf'],
                ['route' => 'viewprofile.index', 'icon' => 'id-card', 'label' => 'Senarai Asnaf'],
                ['route' => 'history', 'icon' => 'clipboard-list', 'label' => 'Permohonan'],
            ]],
            'ai' => ['label' => 'AI & Kelayakan', 'items' => [
                ['route' => 'ai.page', 'icon' => 'sparkles', 'label' => 'AI Assistant'],
                ['route' => 'hadkifayah', 'icon' => 'calculator', 'label' => 'Had Kifayah'],
                ['route' => 'ai-assessment', 'icon' => 'brain', 'label' => 'AI Assessment'],
            ]],
            'data' => ['label' => 'Data', 'items' => [
                ['route' => 'import-pdf.index', 'icon' => 'scan-line', 'label' => 'Import PDF'],
                ['route' => 'reports', 'icon' => 'chart-column', 'label' => 'Laporan'],
            ]],
            'system' => ['label' => 'Sistem', 'items' => [
                ['route' => 'profile', 'icon' => 'user-circle', 'label' => 'Profil'],
                ['route' => 'settings', 'icon' => 'settings', 'label' => 'Tetapan'],
                ['route' => 'admin.invitations.index', 'icon' => 'ticket', 'label' => 'Kod Undangan'],
                ['route' => 'admin.audit-logs.index', 'icon' => 'clipboard-check', 'label' => 'Audit Log'],
                ['route' => 'admin.money-audit.index', 'icon' => 'banknote', 'label' => 'Audit Kewangan'],
            ]],
        ];
        $isSuperAdmin = $user && in_array($user->email, (array) config('app.super_admin_emails'), true);
        if (!$isSuperAdmin) {
            $groups['system']['items'] = array_values(array_filter(
                $groups['system']['items'],
                fn($item) => !in_array($item['route'], ['admin.invitations.index', 'admin.audit-logs.index', 'admin.money-audit.index'], true)
            ));
        }
    }

    $homeRoute = $isPortal ? 'user.dashboard' : 'dashboard';
    $brandSubtitle = $isPortal ? 'Portal Pemohon' : 'Smart Eligibility';
    $roleLabel = $isPortal ? 'Pemohon' : 'Pentadbir';
@endphp

<div x-show="mobileOpen" x-cloak @click="closeMobile()"
     x-transition:enter="transition-opacity duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="md:hidden fixed inset-0 bg-navy/70 backdrop-blur-sm z-40"></div>

<aside x-data="appSidebar"
    :class="{
        'translate-x-0': mobileOpen,
        '-translate-x-full': !mobileOpen,
        'md:translate-x-0': true,
        'md:w-[76px]': collapsed && !mobileOpen,
        'md:w-[260px]': !collapsed || mobileOpen,
    }"
    @toggle-sidebar.window="toggle()"
    class="w-[288px] fixed md:sticky inset-y-0 left-0 z-50 md:z-20 border-r border-white/10 md:rounded-r-[22px] h-screen shrink-0 flex flex-col transition-transform duration-300 ease-out text-white overflow-y-auto md:overflow-hidden"
    style="background: linear-gradient(180deg, #0B1220 0%, #0E1830 55%, #111E3A 100%);">

    {{-- Islamic pattern overlay --}}
    <div class="pattern-mask islamic-pattern opacity-[0.05]"></div>

    <div class="relative z-10 flex flex-col h-full">
        <a href="{{ route($homeRoute) }}" class="h-16 flex items-center px-4 border-b border-white/10 shrink-0 hover:bg-white/5 transition-fast">
            <div class="w-10 h-10 rounded-[13px] bg-white flex items-center justify-center shrink-0 shadow-lg overflow-hidden ring-1 ring-white/20">
                <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-1">
            </div>
            <div x-show="!collapsed" x-cloak class="ml-3 overflow-hidden">
                <p class="text-[15px] font-bold text-white leading-none tracking-tight">J-ZAKAT <span class="text-cyan">AI</span></p>
                <p class="text-[11px] text-white/55 mt-1 leading-none">{{ $brandSubtitle }}</p>
            </div>
        </a>

        @if($user)
        <div class="px-3 py-3 border-b border-white/10 shrink-0">
            <button class="w-full flex items-center gap-3 p-2 rounded-[13px] hover:bg-white/10 transition-fast group border border-transparent hover:border-white/10"
                :title="collapsed ? '{{ $user->name }}' : null">
                <div class="w-10 h-10 rounded-full shrink-0 overflow-hidden ring-2 ring-cyan/40">
                    @if($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full text-white font-semibold text-[14px] flex items-center justify-center" style="background: var(--gradient-royal);">
                            {{ $initials }}
                        </div>
                    @endif
                </div>
                <div x-show="!collapsed" x-cloak class="overflow-hidden min-w-0 text-left flex-1">
                    <p class="text-white font-semibold text-[13.5px] truncate leading-tight">{{ $user->name }}</p>
                    <p class="text-cyan/80 text-[11.5px] truncate leading-tight mt-0.5">{{ $roleLabel }}</p>
                </div>
                <x-icon x-show="!collapsed" x-cloak name="chevron-down" :size="14" class="text-white/40 shrink-0" />
            </button>
        </div>
        @endif

        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @foreach($singleItems as $item)
                @php $active = $isActive($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   :title="collapsed ? '{{ $item['label'] }}' : null"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-[12px] text-[14px] font-medium transition-apple group
                    {{ $active ? 'bg-white/12 text-white shadow-inner' : 'text-white/70 hover:bg-white/8 hover:text-white' }}">
                    @if($active)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 rounded-r-full bg-cyan"></span>
                    @endif
                    <x-icon :name="$item['icon']" :size="18" class="shrink-0 transition-fast {{ $active ? 'text-cyan' : 'text-white/50 group-hover:text-cyan' }}" />
                    <span x-show="!collapsed" x-cloak class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach

            @foreach($groups as $groupKey => $group)
                <button @click="toggleGroup('{{ $groupKey }}')"
                    x-show="!collapsed" x-cloak
                    class="w-full flex items-center justify-between px-3 mt-4 mb-1 first:mt-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-white/40 hover:text-white/70 transition-fast"
                    :aria-expanded="openGroups.includes('{{ $groupKey }}')">
                    <span>{{ $group['label'] }}</span>
                    <x-icon name="chevron-down" :size="13" class="transition-transform duration-200" x-bind:class="openGroups.includes('{{ $groupKey }}') ? '' : '-rotate-90'" />
                </button>
                <div x-show="openGroups.includes('{{ $groupKey }}') || collapsed">
                    @foreach($group['items'] as $item)
                        @php $active = $isActive($item['route']); @endphp
                        <a href="{{ route($item['route']) }}"
                           @if($active) aria-current="page" @endif
                           :title="collapsed ? '{{ $item['label'] }}' : null"
                           class="relative flex items-center gap-3 px-3 py-2 rounded-[11px] text-[13px] font-medium transition-apple group
                            {{ $active ? 'bg-white/12 text-white' : 'text-white/70 hover:bg-white/8 hover:text-white' }}">
                            @if($active)
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 rounded-r-full bg-cyan"></span>
                            @endif
                            <x-icon :name="$item['icon']" :size="17" class="shrink-0 transition-fast {{ $active ? 'text-cyan' : 'text-white/45 group-hover:text-cyan' }}" />
                            <span x-show="!collapsed" x-cloak class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10 shrink-0">
            <button @click="toggle()"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-[12px] text-white/60 hover:bg-white/10 hover:text-white text-[13px] font-medium transition-apple"
                :aria-label="collapsed ? 'Buka sidebar' : 'Tutup sidebar'">
                <x-icon :name="'chevrons-left'" :size="17" class="shrink-0" x-bind:class="collapsed ? 'rotate-180' : ''" />
                <span x-show="!collapsed" x-cloak>Tutup Sidebar</span>
            </button>
        </div>
    </div>
</aside>
