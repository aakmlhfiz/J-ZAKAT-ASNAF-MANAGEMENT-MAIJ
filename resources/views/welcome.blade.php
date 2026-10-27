{{--
    Public landing page — J-ZAKAT
    Data passed from the `/` route via WelcomePreviewService::getAll():
      $stats (total, layak, tidak_layak, dalam_semakan), $chartLabels, $chartValues, $aiPick
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('landing.meta.title') }}</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing antialiased">

<div x-data="{ mobileNav: false, scrolled: false }"
     @scroll.window="scrolled = window.scrollY > 12"
     class="relative">

    {{-- ============================== NAV ============================== --}}
    <header class="landing-nav" :class="scrolled ? 'is-scrolled' : ''">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="flex items-center justify-between h-16 md:h-[72px]">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group shrink-0">
                    <div class="w-10 h-10 rounded-[13px] bg-white flex items-center justify-center shadow-lg ring-1 ring-white/20 overflow-hidden">
                        <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-1">
                    </div>
                    <div class="leading-none">
                        <p class="text-[16px] font-bold text-white tracking-tight whitespace-nowrap">J-ZAKAT</p>
                        <p class="hidden xl:block text-[10.5px] text-white/50 mt-1 tracking-wider uppercase whitespace-nowrap">Majlis Agama Islam Johor</p>
                    </div>
                </a>

                <nav class="hidden xl:flex items-center gap-1">
                    @foreach(__('landing.nav.items') as $navItem)
                        <a href="{{ $navItem['href'] }}" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast whitespace-nowrap">{{ $navItem['label'] }}</a>
                    @endforeach
                </nav>

                <div class="hidden xl:flex items-center gap-2.5">
                    <x-language-switcher variant="dark" />
                    <a href="{{ route('user.login') }}" class="btn btn-sm btn-glass">
                        <x-icon name="log-in" :size="15" /> {{ __('landing.nav.login') }}
                    </a>
                    <a href="{{ route('user.register') }}" class="btn btn-sm btn-gradient">
                        <x-icon name="hand-heart" :size="15" /> {{ __('landing.nav.apply') }}
                    </a>
                </div>

                <button @click="mobileNav = !mobileNav" class="xl:hidden w-10 h-10 rounded-[11px] bg-white/8 hover:bg-white/12 flex items-center justify-center text-white transition-fast" aria-label="Menu">
                    <x-icon x-show="!mobileNav" name="menu" :size="20" />
                    <x-icon x-show="mobileNav" x-cloak name="x" :size="20" />
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="mobileNav" x-cloak x-transition
             class="xl:hidden border-t border-white/10 bg-[#0B1220]/95 backdrop-blur-xl">
            <div class="px-5 py-4 space-y-1">
                @foreach(__('landing.nav.items') as $navItem)
                    <a href="{{ $navItem['href'] }}" @click="mobileNav=false" class="block px-3 py-2.5 rounded-[11px] text-[14px] font-medium text-white/80 hover:text-white hover:bg-white/8 transition-fast">{{ $navItem['label'] }}</a>
                @endforeach
                <div class="pt-2 space-y-2">
                    <div class="flex justify-center pb-1"><x-language-switcher variant="dark" /></div>
                    <a href="{{ route('user.login') }}" class="btn btn-md btn-glass w-full"><x-icon name="log-in" :size="16" /> {{ __('landing.nav.portal_login') }}</a>
                    <a href="{{ route('login') }}" class="btn btn-md btn-glass w-full"><x-icon name="shield-check" :size="16" /> {{ __('landing.nav.admin_login') }}</a>
                    <a href="{{ route('user.register') }}" class="btn btn-md btn-gradient w-full"><x-icon name="hand-heart" :size="16" /> {{ __('landing.nav.apply') }}</a>
                </div>
            </div>
        </div>
    </header>

    {{-- ============================== HERO ============================== --}}
    <section id="utama" class="landing-hero">
        <div class="landing-hero__photo"></div>
        <div class="landing-hero__glow"></div>

        <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-16 md:pt-24 pb-20 md:pb-28">
            <div class="grid lg:grid-cols-[1.15fr_0.85fr] gap-12 lg:gap-16 items-center">
                <div class="reveal">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-pill text-[11.5px] font-semibold tracking-wide uppercase bg-cyan/10 text-cyan border border-cyan/25">
                        <x-icon name="sparkles" :size="13" /> {{ __('landing.hero.badge') }}
                    </span>

                    <h1 class="mt-6 text-[34px] leading-[1.12] sm:text-[44px] sm:leading-[1.1] lg:text-[52px] font-bold tracking-tight text-white">
                        J-ZAKAT —
                        <span class="text-gradient-royal">{{ __('landing.hero.title') }}</span>
                    </h1>

                    <p class="mt-5 text-[15.5px] leading-relaxed text-white/70 max-w-xl">
                        {{ __('landing.hero.lead_before') }}
                        <span class="text-white/90 font-medium">{{ __('landing.hero.organisation') }}</span>
                        {{ __('landing.hero.lead_after') }}
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient">
                            <x-icon name="hand-heart" :size="19" /> {{ __('landing.hero.apply') }}
                        </a>
                        <a href="{{ route('user.login') }}" class="btn btn-lg btn-glass">
                            <x-icon name="log-in" :size="19" /> {{ __('landing.hero.login') }}
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-[12.5px] text-white/55">
                        <span class="inline-flex items-center gap-2"><x-icon name="shield-check" :size="15" class="text-emerald" /> {{ __('landing.hero.trust.secure') }}</span>
                        <span class="inline-flex items-center gap-2"><x-icon name="activity" :size="15" class="text-cyan" /> {{ __('landing.hero.trust.assessment') }}</span>
                        <span class="inline-flex items-center gap-2"><x-icon name="eye" :size="15" class="text-cyan" /> {{ __('landing.hero.trust.audit') }}</span>
                    </div>
                </div>

                {{-- Hero visual card --}}
                <div class="reveal hidden lg:block">
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-[32px] opacity-40 blur-2xl" style="background: radial-gradient(circle at 60% 40%, rgba(56,189,248,0.35), transparent 65%);"></div>
                        <div class="relative rounded-[24px] border border-white/12 bg-[#0F1B33]/70 backdrop-blur-xl p-6 shadow-2xl">
                            <div class="flex items-center justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-[13px] flex items-center justify-center text-white" style="background: var(--gradient-royal);">
                                        <x-icon name="brain" :size="22" />
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-white">{{ __('landing.hero.card.title') }}</p>
                                        <p class="text-[11px] text-white/50">{{ __('landing.hero.card.subtitle') }}</p>
                                    </div>
                                </div>
                                <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-success"></span> {{ __('landing.hero.card.active') }}</span>
                            </div>

                            @php $total = max((int)($stats['total'] ?? 0), 0); @endphp
                            <div class="space-y-3">
                                @php
                                    $heroRows = [
                                        ['label' => __('landing.stats.layak'), 'value' => (int)($stats['layak'] ?? 0), 'color' => 'var(--color-success)', 'icon' => 'circle-check'],
                                        ['label' => __('landing.stats.tidak_layak'), 'value' => (int)($stats['tidak_layak'] ?? 0), 'color' => 'var(--color-danger)', 'icon' => 'circle-x'],
                                        ['label' => __('landing.stats.dalam_semakan'), 'value' => (int)($stats['dalam_semakan'] ?? 0), 'color' => 'var(--color-warning)', 'icon' => 'clock'],
                                    ];
                                @endphp
                                @foreach($heroRows as $row)
                                    <div class="flex items-center justify-between rounded-[14px] bg-white/[0.04] border border-white/8 px-4 py-3">
                                        <span class="flex items-center gap-2.5 text-[13px] text-white/80">
                                            <x-icon :name="$row['icon']" :size="16" style="color: {{ $row['color'] }};" />
                                            {{ $row['label'] }}
                                        </span>
                                        <span class="text-[15px] font-bold text-white tabular-nums">{{ number_format($row['value']) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-[12px] text-white/50">{{ __('landing.hero.card.total') }}</span>
                                <span class="text-[18px] font-bold text-cyan tabular-nums">{{ number_format((int)($stats['total'] ?? 0)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== FEATURES ============================== --}}
    <section id="ciri" class="relative py-20 md:py-24 bg-[#0B1220]">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="reveal max-w-2xl">
                <p class="text-eyebrow text-cyan mb-3">{{ __('landing.features.eyebrow') }}</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">{{ __('landing.features.title') }}</h2>
                <p class="mt-4 text-[15px] text-white/60">{{ __('landing.features.subtitle') }}</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12 stagger">
                @php
                    $features = [
                        ['icon' => 'brain', 'title' => __('landing.features.ai.title'), 'desc' => __('landing.features.ai.desc')],
                        ['icon' => 'calculator', 'title' => __('landing.features.kifayah.title'), 'desc' => __('landing.features.kifayah.desc')],
                        ['icon' => 'file-plus', 'title' => __('landing.features.application.title'), 'desc' => __('landing.features.application.desc')],
                        ['icon' => 'chart-line', 'title' => __('landing.features.tracking.title'), 'desc' => __('landing.features.tracking.desc')],
                    ];
                @endphp
                @foreach($features as $f)
                    <div class="landing-card p-6 reveal">
                        <div class="landing-icon">
                            <x-icon :name="$f['icon']" :size="23" />
                        </div>
                        <h3 class="mt-5 text-[16px] font-semibold text-white">{{ $f['title'] }}</h3>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-white/55">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== STATISTICS ============================== --}}
    <section id="statistik" class="py-20 md:py-24" style="background: linear-gradient(180deg, #0B1220 0%, #0E1830 100%);">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="reveal text-center max-w-2xl mx-auto">
                <p class="text-eyebrow text-cyan mb-3">{{ __('landing.stats.eyebrow') }}</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">{{ __('landing.stats.title') }}</h2>
                <p class="mt-4 text-[15px] text-white/60">{{ __('landing.stats.subtitle') }}</p>
            </div>

            @php
                $statCards = [
                    ['label' => __('landing.stats.total'), 'value' => (int)($stats['total'] ?? 0), 'icon' => 'clipboard-list', 'color' => 'var(--color-cyan)'],
                    ['label' => __('landing.stats.layak'), 'value' => (int)($stats['layak'] ?? 0), 'icon' => 'circle-check', 'color' => 'var(--color-success)'],
                    ['label' => __('landing.stats.tidak_layak'), 'value' => (int)($stats['tidak_layak'] ?? 0), 'icon' => 'circle-x', 'color' => 'var(--color-danger)'],
                    ['label' => __('landing.stats.dalam_semakan'), 'value' => (int)($stats['dalam_semakan'] ?? 0), 'icon' => 'clock', 'color' => 'var(--color-warning)'],
                ];
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-12 stagger">
                @foreach($statCards as $s)
                    <div class="landing-stat reveal">
                        <div class="w-12 h-12 rounded-[14px] mx-auto flex items-center justify-center mb-4" style="background: color-mix(in srgb, {{ $s['color'] }} 14%, transparent); color: {{ $s['color'] }};">
                            <x-icon :name="$s['icon']" :size="22" />
                        </div>
                        <p class="text-[32px] font-bold tracking-tight text-white tabular-nums leading-none">{{ number_format($s['value']) }}</p>
                        <p class="text-[12.5px] text-white/55 mt-2">{{ $s['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Genuine monthly trend (CSS bars) --}}
            @php
                $chartValues = array_values(array_map('intval', $chartValues ?? []));
                $chartLabels = array_values($chartLabels ?? []);
                $maxVal = !empty($chartValues) ? max($chartValues) : 0;
                if ($maxVal <= 0) { $maxVal = 1; }
            @endphp
            @if(!empty($chartValues) && (int) ($stats['total'] ?? 0) > 0)
            <div class="reveal mt-10 landing-card p-6">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <h3 class="text-[15px] font-semibold text-white">{{ __('landing.stats.trend_title') }}</h3>
                        <p class="text-[12px] text-white/50 mt-1">{{ __('landing.stats.trend_subtitle') }}</p>
                    </div>
                    <span class="badge badge-info"><x-icon name="activity" :size="13" /> {{ __('landing.stats.live') }}</span>
                </div>
                <div class="flex items-end gap-1.5 sm:gap-2.5 h-40">
                    @foreach($chartValues as $i => $v)
                        <div class="flex-1 flex flex-col items-center justify-end gap-2 group">
                            <span class="text-[10px] text-white/40 opacity-0 group-hover:opacity-100 transition-fast tabular-nums">{{ $v }}</span>
                            <div class="w-full rounded-t-[5px] transition-all duration-500" style="height: {{ max(4, (int) round(($v / $maxVal) * 130)) }}px; background: linear-gradient(180deg, #38BDF8, #2563EB); opacity: {{ $v > 0 ? '1' : '0.25' }};"></div>
                            <span class="text-[10px] text-white/40">{{ $chartLabels[$i] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- ============================== HOW TO APPLY ============================== --}}
    <section id="cara" class="py-20 md:py-24 bg-[#0B1220]">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="reveal max-w-2xl">
                <p class="text-eyebrow text-cyan mb-3">{{ __('landing.how.eyebrow') }}</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">{{ __('landing.how.title') }}</h2>
            </div>

            @php
                $steps = [
                    ['title' => __('landing.how.steps.register.title'), 'desc' => __('landing.how.steps.register.desc')],
                    ['title' => __('landing.how.steps.complete.title'), 'desc' => __('landing.how.steps.complete.desc')],
                    ['title' => __('landing.how.steps.review.title'), 'desc' => __('landing.how.steps.review.desc')],
                    ['title' => __('landing.how.steps.result.title'), 'desc' => __('landing.how.steps.result.desc')],
                ];
            @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12 stagger">
                @foreach($steps as $i => $step)
                    <div class="reveal relative">
                        <div class="landing-step__num">{{ $i + 1 }}</div>
                        <h3 class="mt-5 text-[16px] font-semibold text-white">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-white/55">{{ $step['desc'] }}</p>
                        @if($i < count($steps) - 1)
                            <div class="hidden lg:block absolute top-6 left-[54px] right-[-1.25rem] h-px bg-gradient-to-r from-cyan/40 to-transparent"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="reveal mt-12 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient"><x-icon name="hand-heart" :size="19" /> {{ __('landing.how.start') }}</a>
                <a href="#faq" class="btn btn-lg btn-glass"><x-icon name="circle-help" :size="19" /> {{ __('landing.how.faq') }}</a>
            </div>
        </div>
    </section>

    {{-- ============================== FAQ ============================== --}}
    <section id="faq" class="py-20 md:py-24" style="background: linear-gradient(180deg, #0B1220 0%, #0E1830 100%);">
        <div class="max-w-3xl mx-auto px-5 md:px-8">
            <div class="reveal text-center">
                <p class="text-eyebrow text-cyan mb-3">{{ __('landing.faq.eyebrow') }}</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">{{ __('landing.faq.title') }}</h2>
            </div>

            @php
                $faqs = [
                    ['q' => __('landing.faq.items.eligibility.q'), 'a' => __('landing.faq.items.eligibility.a')],
                    ['q' => __('landing.faq.items.documents.q'), 'a' => __('landing.faq.items.documents.a')],
                    ['q' => __('landing.faq.items.kifayah.q'), 'a' => __('landing.faq.items.kifayah.a')],
                    ['q' => __('landing.faq.items.duration.q'), 'a' => __('landing.faq.items.duration.a')],
                ];
            @endphp

            <div class="mt-10 space-y-3">
                @foreach($faqs as $faq)
                    <details class="landing-faq reveal landing-card group">
                        <summary class="flex items-center justify-between gap-4 px-5 py-4">
                            <span class="text-[14.5px] font-medium text-white">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" :size="18" class="landing-faq__chevron text-cyan shrink-0 transition-transform duration-200" />
                        </summary>
                        <div class="px-5 pb-5 -mt-1 text-[13.5px] leading-relaxed text-white/60">{{ $faq['a'] }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== CTA BANNER ============================== --}}
    <section class="pb-20">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="reveal relative overflow-hidden rounded-[24px] p-8 md:p-12" style="background: var(--gradient-brand);">
                <div class="pattern-mask islamic-pattern opacity-[0.08]"></div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="max-w-xl">
                        <h2 class="text-[26px] sm:text-[30px] font-bold tracking-tight text-white leading-tight">{{ __('landing.cta.title') }}</h2>
                        <p class="mt-3 text-[14.5px] text-white/70">{{ __('landing.cta.subtitle') }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                        <a href="{{ route('user.register') }}" class="btn btn-lg bg-white text-navy hover:bg-white/90"><x-icon name="hand-heart" :size="19" /> {{ __('landing.cta.register') }}</a>
                        <a href="{{ route('user.login') }}" class="btn btn-lg btn-glass"><x-icon name="log-in" :size="19" /> {{ __('landing.cta.login') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== FOOTER ============================== --}}
    <footer id="hubungi" class="border-t border-white/10 bg-[#080E1A]">
        <div class="max-w-7xl mx-auto px-5 md:px-8 py-14">
            <div class="grid md:grid-cols-4 gap-10">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-white flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-1">
                        </div>
                        <div class="leading-none">
                            <p class="text-[15px] font-bold text-white tracking-tight">J-ZAKAT</p>
                            <p class="text-[10.5px] text-white/45 mt-1 tracking-wider uppercase">{{ __('landing.footer.tagline') }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-[13.5px] leading-relaxed text-white/50 max-w-md">
                        {{ __('landing.footer.description') }}
                    </p>
                    <div class="mt-6 flex items-start gap-3 text-[13px] text-white/55">
                        <x-icon name="map-pin" :size="17" class="text-cyan shrink-0 mt-0.5" />
                        <span>{!! __('landing.footer.address') !!}</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2.5 text-[13px] text-white/55">
                        <x-icon name="phone" :size="16" class="text-cyan" />
                        <span>{{ __('landing.footer.phone') }}</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-[12px] font-semibold uppercase tracking-wider text-white/40 mb-4">{{ __('landing.footer.quick_links') }}</h4>
                    <ul class="space-y-2.5 text-[13.5px]">
                        <li><a href="#ciri" class="landing-section-link">{{ __('landing.footer.services') }}</a></li>
                        <li><a href="#cara" class="landing-section-link">{{ __('landing.footer.how') }}</a></li>
                        <li><a href="#faq" class="landing-section-link">{{ __('landing.footer.faq') }}</a></li>
                        <li><a href="{{ route('user.hadkifayah') }}" class="landing-section-link">{{ __('landing.footer.calculate') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[12px] font-semibold uppercase tracking-wider text-white/40 mb-4">{{ __('landing.footer.account') }}</h4>
                    <ul class="space-y-2.5 text-[13.5px]">
                        <li><a href="{{ route('user.register') }}" class="landing-section-link">{{ __('landing.footer.register') }}</a></li>
                        <li><a href="{{ route('user.login') }}" class="landing-section-link">{{ __('landing.footer.portal_login') }}</a></li>
                        <li><a href="{{ route('login') }}" class="landing-section-link">{{ __('landing.footer.admin_login') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="landing-divider my-8"></div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[12.5px] text-white/40">
                <p>{{ __('landing.footer.copyright', ['year' => date('Y')]) }}</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-white/70 transition-fast">{{ __('landing.footer.privacy') }}</a>
                    <a href="#" class="hover:text-white/70 transition-fast">{{ __('landing.footer.terms') }}</a>
                    <a href="#" class="hover:text-white/70 transition-fast">{{ __('landing.footer.about') }}</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
    // Reveal-on-scroll for landing sections
    (function () {
        var els = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        els.forEach(function (el) { io.observe(el); });
    })();
</script>
</body>
</html>
