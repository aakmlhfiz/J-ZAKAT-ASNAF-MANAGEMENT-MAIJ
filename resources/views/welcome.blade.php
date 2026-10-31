{{--
    Public landing page — J-ZAKAT
    Data passed from the `/` route via WelcomePreviewService::getAll():
      $stats (total, layak, tidak_layak, dalam_semakan), $chartLabels, $chartValues, $aiPick
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F5F7FA">
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('landing.meta.title') }}</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FA] text-[#1F2937] antialiased">

<div x-data="{ mobileNav: false }" class="relative">
    <header class="sticky top-0 z-40 bg-white border-b border-[#E2E8F0]">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="flex items-center justify-between h-16 md:h-[68px]">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-9 h-9 rounded-[8px] border border-[#E2E8F0] bg-white flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-0.5">
                    </div>
                    <div class="leading-none">
                        <p class="text-[15px] font-semibold text-[#1F2937] tracking-tight">J-ZAKAT</p>
                        <p class="text-[9.5px] text-[#5B6B7F] mt-1 tracking-[0.12em] uppercase">Majlis Agama Islam Johor</p>
                    </div>
                </a>

                <nav class="hidden xl:flex items-center gap-1">
                    @foreach(__('landing.nav.items') as $navItem)
                        <a href="{{ $navItem['href'] }}" class="px-3 py-2 rounded-[6px] text-[13px] font-medium text-[#5B6B7F] hover:text-[#15345B] hover:bg-[#F1F5F9] transition-fast whitespace-nowrap">{{ $navItem['label'] }}</a>
                    @endforeach
                </nav>

                <div class="hidden xl:flex items-center gap-2">
                    <x-language-switcher variant="light" />
                    <a href="{{ route('user.login') }}" class="btn btn-sm btn-glass">
                        <x-icon name="log-in" :size="15" /> {{ __('landing.nav.login') }}
                    </a>
                    <a href="{{ route('user.register') }}" class="btn btn-sm btn-gradient">
                        <x-icon name="hand-heart" :size="15" /> {{ __('landing.nav.apply') }}
                    </a>
                </div>

                <button @click="mobileNav = !mobileNav" class="xl:hidden w-9 h-9 rounded-[6px] border border-[#E2E8F0] hover:bg-[#F1F5F9] flex items-center justify-center text-[#475569] transition-fast" aria-label="Menu">
                    <x-icon x-show="!mobileNav" name="menu" :size="19" />
                    <x-icon x-show="mobileNav" x-cloak name="x" :size="19" />
                </button>
            </div>
        </div>

        <div x-show="mobileNav" x-cloak x-transition class="xl:hidden border-t border-[#E2E8F0] bg-white">
            <div class="px-5 py-4 space-y-1">
                @foreach(__('landing.nav.items') as $navItem)
                    <a href="{{ $navItem['href'] }}" @click="mobileNav=false" class="block px-3 py-2.5 rounded-[6px] text-[14px] font-medium text-[#475569] hover:text-[#15345B] hover:bg-[#F1F5F9] transition-fast">{{ $navItem['label'] }}</a>
                @endforeach
                <div class="pt-3 space-y-2 border-t border-[#E2E8F0]">
                    <div class="flex justify-center pb-1"><x-language-switcher variant="light" /></div>
                    <a href="{{ route('user.login') }}" class="btn btn-md btn-glass w-full"><x-icon name="log-in" :size="16" /> {{ __('landing.nav.portal_login') }}</a>
                    <a href="{{ route('login') }}" class="btn btn-md btn-glass w-full"><x-icon name="shield-check" :size="16" /> {{ __('landing.nav.admin_login') }}</a>
                    <a href="{{ route('user.register') }}" class="btn btn-md btn-gradient w-full"><x-icon name="hand-heart" :size="16" /> {{ __('landing.nav.apply') }}</a>
                </div>
            </div>
        </div>
    </header>

    <main>
    {{-- HERO --}}
    <section class="bg-white border-b border-[#E2E8F0]">
        <div class="max-w-7xl mx-auto px-5 md:px-8 py-12 md:py-16 grid lg:grid-cols-[1.05fr_0.95fr] gap-10 lg:gap-14 items-center">
            <div class="animate-fade-in">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] text-[11px] font-semibold tracking-wide uppercase bg-[#EAF1F8] text-[#245B91] border border-[#D5E3F1]">
                    <x-icon name="landmark" :size="13" /> {{ __('landing.hero.badge') }}
                </span>

                <h1 class="mt-5 text-[32px] leading-[1.12] sm:text-[38px] lg:text-[44px] font-semibold tracking-[-0.025em] text-[#1F2937]">
                    J-ZAKAT —
                    <span class="text-[#15345B]">{{ __('landing.hero.title') }}</span>
                </h1>

                <p class="mt-5 text-[15px] leading-relaxed text-[#5B6B7F] max-w-xl">
                    {{ __('landing.hero.lead_before') }}
                    <span class="text-[#334155] font-medium">{{ __('landing.hero.organisation') }}</span>
                    {{ __('landing.hero.lead_after') }}
                </p>

                <div class="mt-7 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient">
                        <x-icon name="hand-heart" :size="18" /> {{ __('landing.hero.apply') }}
                    </a>
                    <a href="{{ route('user.login') }}" class="btn btn-lg btn-glass">
                        <x-icon name="log-in" :size="18" /> {{ __('landing.hero.login') }}
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12.5px] text-[#5B6B7F]">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="shield-check" :size="15" class="text-[#15803D]" /> {{ __('landing.hero.trust.secure') }}</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="activity" :size="15" class="text-[#245B91]" /> {{ __('landing.hero.trust.assessment') }}</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="eye" :size="15" class="text-[#245B91]" /> {{ __('landing.hero.trust.audit') }}</span>
                </div>
            </div>

            <div class="animate-fade-in">
                <img src="{{ asset('images/masjid-day.jpg') }}" alt="Masjid Sultan Abu Bakar, Johor Bahru" class="w-full h-[280px] md:h-[360px] object-cover rounded-[12px] border border-[#E2E8F0]">
                <div class="mt-3 flex items-center justify-between gap-4 text-[11.5px] text-[#5B6B7F]">
                    <span>{{ __('landing.hero.organisation') }}</span>
                    <span>Johor Bahru</span>
                </div>
            </div>
        </div>
    </section>

    {{-- REAL OPERATIONAL STATISTICS --}}
    <section class="bg-[#F5F7FA] border-b border-[#E2E8F0]">
        <div class="max-w-7xl mx-auto px-5 md:px-8 py-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['label' => __('landing.stats.total'), 'value' => (int) ($stats['total'] ?? 0), 'color' => 'text-[#15345B]'],
                    ['label' => __('landing.stats.layak'), 'value' => (int) ($stats['layak'] ?? 0), 'color' => 'text-[#15803D]'],
                    ['label' => __('landing.stats.tidak_layak'), 'value' => (int) ($stats['tidak_layak'] ?? 0), 'color' => 'text-[#DC2626]'],
                    ['label' => __('landing.stats.dalam_semakan'), 'value' => (int) ($stats['dalam_semakan'] ?? 0), 'color' => 'text-[#B45309]'],
                ] as $stat)
                    <div class="border-l-2 border-[#D5E3F1] pl-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[#5B6B7F]">{{ $stat['label'] }}</p>
                        <p class="mt-1.5 text-[28px] font-semibold tabular-nums {{ $stat['color'] }}">{{ number_format($stat['value']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SERVICES --}}
    <section id="ciri" class="bg-white py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#245B91]">{{ __('landing.features.eyebrow') }}</p>
                <h2 class="mt-2 text-[26px] sm:text-[30px] font-semibold tracking-[-0.02em] text-[#1F2937] leading-tight">{{ __('landing.features.title') }}</h2>
                <p class="mt-3 text-[14.5px] text-[#5B6B7F]">{{ __('landing.features.subtitle') }}</p>
            </div>

            @php
                $features = [
                    ['icon' => 'clipboard-check', 'title' => __('landing.features.ai.title'), 'desc' => __('landing.features.ai.desc')],
                    ['icon' => 'calculator', 'title' => __('landing.features.kifayah.title'), 'desc' => __('landing.features.kifayah.desc')],
                    ['icon' => 'file-plus', 'title' => __('landing.features.application.title'), 'desc' => __('landing.features.application.desc')],
                    ['icon' => 'chart-line', 'title' => __('landing.features.tracking.title'), 'desc' => __('landing.features.tracking.desc')],
                ];
            @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-10">
                @foreach($features as $feature)
                    <div class="border border-[#E2E8F0] rounded-[10px] bg-white p-5">
                        <div class="w-9 h-9 rounded-[8px] bg-[#EAF1F8] text-[#245B91] flex items-center justify-center">
                            <x-icon :name="$feature['icon']" :size="19" />
                        </div>
                        <h3 class="mt-4 text-[15px] font-semibold text-[#1F2937]">{{ $feature['title'] }}</h3>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-[#5B6B7F]">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW TO APPLY --}}
    <section id="cara" class="bg-[#F5F7FA] py-16 md:py-20 border-y border-[#E2E8F0]">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#245B91]">{{ __('landing.how.eyebrow') }}</p>
                <h2 class="mt-2 text-[26px] sm:text-[30px] font-semibold tracking-[-0.02em] text-[#1F2937] leading-tight">{{ __('landing.how.title') }}</h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
                @foreach([
                    ['title' => __('landing.how.steps.register.title'), 'desc' => __('landing.how.steps.register.desc')],
                    ['title' => __('landing.how.steps.complete.title'), 'desc' => __('landing.how.steps.complete.desc')],
                    ['title' => __('landing.how.steps.review.title'), 'desc' => __('landing.how.steps.review.desc')],
                    ['title' => __('landing.how.steps.result.title'), 'desc' => __('landing.how.steps.result.desc')],
                ] as $index => $step)
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full bg-[#15345B] text-white flex items-center justify-center text-[12px] font-semibold">{{ $index + 1 }}</div>
                        <h3 class="mt-4 text-[15px] font-semibold text-[#1F2937]">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-[#5B6B7F]">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient"><x-icon name="hand-heart" :size="18" /> {{ __('landing.how.start') }}</a>
                <a href="#faq" class="btn btn-lg btn-glass"><x-icon name="circle-help" :size="18" /> {{ __('landing.how.faq') }}</a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="bg-white py-16 md:py-20">
        <div class="max-w-3xl mx-auto px-5 md:px-8">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#245B91]">{{ __('landing.faq.eyebrow') }}</p>
                <h2 class="mt-2 text-[26px] sm:text-[30px] font-semibold tracking-[-0.02em] text-[#1F2937] leading-tight">{{ __('landing.faq.title') }}</h2>
            </div>

            <div class="mt-8 space-y-3">
                @foreach([
                    ['q' => __('landing.faq.items.eligibility.q'), 'a' => __('landing.faq.items.eligibility.a')],
                    ['q' => __('landing.faq.items.documents.q'), 'a' => __('landing.faq.items.documents.a')],
                    ['q' => __('landing.faq.items.kifayah.q'), 'a' => __('landing.faq.items.kifayah.a')],
                    ['q' => __('landing.faq.items.duration.q'), 'a' => __('landing.faq.items.duration.a')],
                ] as $faq)
                    <details class="border border-[#E2E8F0] rounded-[8px] bg-white group">
                        <summary class="flex items-center justify-between gap-4 px-4 py-3.5 cursor-pointer list-none">
                            <span class="text-[14px] font-medium text-[#1F2937]">{{ $faq['q'] }}</span>
                            <x-icon name="chevron-down" :size="17" class="text-[#5B6B7F] shrink-0 transition-transform duration-200 group-open:rotate-180" />
                        </summary>
                        <div class="px-4 pb-4 text-[13.5px] leading-relaxed text-[#5B6B7F]">{{ $faq['a'] }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-[#15345B] py-12 md:py-14">
        <div class="max-w-7xl mx-auto px-5 md:px-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="max-w-xl">
                <h2 class="text-[24px] sm:text-[28px] font-semibold tracking-[-0.02em] text-white leading-tight">{{ __('landing.cta.title') }}</h2>
                <p class="mt-2 text-[14px] text-white/70">{{ __('landing.cta.subtitle') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                <a href="{{ route('user.register') }}" class="btn btn-lg bg-white text-[#15345B] hover:bg-[#F1F5F9]"><x-icon name="hand-heart" :size="18" /> {{ __('landing.cta.register') }}</a>
                <a href="{{ route('user.login') }}" class="btn btn-lg border border-white/25 text-white hover:bg-white/10"><x-icon name="log-in" :size="18" /> {{ __('landing.cta.login') }}</a>
            </div>
        </div>
    </section>

    </main>

    {{-- FOOTER --}}
    <footer id="hubungi" class="bg-white border-t border-[#E2E8F0]">
        <div class="max-w-7xl mx-auto px-5 md:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[8px] border border-[#E2E8F0] bg-white flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-0.5">
                        </div>
                        <div class="leading-none">
                            <p class="text-[14px] font-semibold text-[#1F2937]">J-ZAKAT</p>
                            <p class="text-[9.5px] text-[#5B6B7F] mt-1 tracking-[0.12em] uppercase">{{ __('landing.footer.tagline') }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-[13px] leading-relaxed text-[#5B6B7F] max-w-md">{{ __('landing.footer.description') }}</p>
                    <div class="mt-5 flex items-start gap-2.5 text-[12.5px] text-[#5B6B7F]">
                        <x-icon name="map-pin" :size="16" class="text-[#245B91] shrink-0 mt-0.5" />
                        <span>{!! __('landing.footer.address') !!}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-2.5 text-[12.5px] text-[#5B6B7F]">
                        <x-icon name="phone" :size="15" class="text-[#245B91]" />
                        <span>{{ __('landing.footer.phone') }}</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#5B6B7F] mb-3">{{ __('landing.footer.quick_links') }}</h3>
                    <ul class="space-y-2 text-[13px]">
                        <li><a href="#ciri" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.services') }}</a></li>
                        <li><a href="#cara" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.how') }}</a></li>
                        <li><a href="#faq" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.faq') }}</a></li>
                        <li><a href="{{ route('user.hadkifayah') }}" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.calculate') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#5B6B7F] mb-3">{{ __('landing.footer.account') }}</h3>
                    <ul class="space-y-2 text-[13px]">
                        <li><a href="{{ route('user.register') }}" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.register') }}</a></li>
                        <li><a href="{{ route('user.login') }}" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.portal_login') }}</a></li>
                        <li><a href="{{ route('login') }}" class="text-[#5B6B7F] hover:text-[#15345B]">{{ __('landing.footer.admin_login') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-[#E2E8F0] flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-[#5B6B7F]">
                <p>{{ __('landing.footer.copyright', ['year' => date('Y')]) }}</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-[#15345B]">{{ __('landing.footer.privacy') }}</a>
                    <a href="#" class="hover:text-[#15345B]">{{ __('landing.footer.terms') }}</a>
                    <a href="#" class="hover:text-[#15345B]">{{ __('landing.footer.about') }}</a>
                </div>
            </div>
        </div>
    </footer>
</div>

</body>
</html>
