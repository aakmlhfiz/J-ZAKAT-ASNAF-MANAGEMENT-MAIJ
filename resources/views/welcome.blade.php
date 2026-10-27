{{--
    Public landing page — J-ZAKAT
    Data passed from the `/` route via WelcomePreviewService::getAll():
      $stats (total, layak, tidak_layak, dalam_semakan), $chartLabels, $chartValues, $aiPick
--}}
<!DOCTYPE html>
<html lang="ms" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <meta name="description" content="J-ZAKAT — Platform Pintar Pengurusan Zakat Majlis Agama Islam Johor. Permohonan bantuan zakat dalam talian dengan penilaian Had Kifayah dan analisis AI.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>J-ZAKAT — Platform Pintar Pengurusan Zakat</title>
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
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-[13px] bg-white flex items-center justify-center shadow-lg ring-1 ring-white/20 overflow-hidden">
                        <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-1">
                    </div>
                    <div class="leading-none">
                        <p class="text-[16px] font-bold text-white tracking-tight">J-ZAKAT</p>
                        <p class="text-[10.5px] text-white/50 mt-1 tracking-wider uppercase">Majlis Agama Islam Johor</p>
                    </div>
                </a>

                <nav class="hidden lg:flex items-center gap-1">
                    <a href="#utama" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">Home</a>
                    <a href="#ciri" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">Perkhidmatan</a>
                    <a href="#statistik" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">Statistik</a>
                    <a href="#cara" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">Cara Mohon</a>
                    <a href="#faq" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">FAQ</a>
                    <a href="#hubungi" class="px-3.5 py-2 rounded-[11px] text-[13.5px] font-medium text-white/75 hover:text-white hover:bg-white/8 transition-fast">Hubungi</a>
                </nav>

                <div class="hidden lg:flex items-center gap-2.5">
                    <a href="{{ route('user.login') }}" class="btn btn-sm btn-glass">
                        <x-icon name="log-in" :size="15" /> Log Masuk
                    </a>
                    <a href="{{ route('user.register') }}" class="btn btn-sm btn-gradient">
                        <x-icon name="hand-heart" :size="15" /> Mohon Bantuan Zakat
                    </a>
                </div>

                <button @click="mobileNav = !mobileNav" class="lg:hidden w-10 h-10 rounded-[11px] bg-white/8 hover:bg-white/12 flex items-center justify-center text-white transition-fast" aria-label="Menu">
                    <x-icon x-show="!mobileNav" name="menu" :size="20" />
                    <x-icon x-show="mobileNav" x-cloak name="x" :size="20" />
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="mobileNav" x-cloak x-transition
             class="lg:hidden border-t border-white/10 bg-[#0B1220]/95 backdrop-blur-xl">
            <div class="px-5 py-4 space-y-1">
                @foreach([['#utama','Home'],['#ciri','Perkhidmatan'],['#statistik','Statistik'],['#cara','Cara Mohon'],['#faq','FAQ'],['#hubungi','Hubungi']] as [$href,$label])
                    <a href="{{ $href }}" @click="mobileNav=false" class="block px-3 py-2.5 rounded-[11px] text-[14px] font-medium text-white/80 hover:text-white hover:bg-white/8 transition-fast">{{ $label }}</a>
                @endforeach
                <div class="pt-2 space-y-2">
                    <a href="{{ route('user.login') }}" class="btn btn-md btn-glass w-full"><x-icon name="log-in" :size="16" /> Log Masuk Pemohon</a>
                    <a href="{{ route('login') }}" class="btn btn-md btn-glass w-full"><x-icon name="shield-check" :size="16" /> Log Masuk Pentadbir</a>
                    <a href="{{ route('user.register') }}" class="btn btn-md btn-gradient w-full"><x-icon name="hand-heart" :size="16" /> Mohon Bantuan Zakat</a>
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
                        <x-icon name="sparkles" :size="13" /> Platform Zakat Berkuasa AI
                    </span>

                    <h1 class="mt-6 text-[34px] leading-[1.12] sm:text-[44px] sm:leading-[1.1] lg:text-[52px] font-bold tracking-tight text-white">
                        J-ZAKAT —
                        <span class="text-gradient-royal">Platform Pintar Pengurusan Zakat</span>
                    </h1>

                    <p class="mt-5 text-[15.5px] leading-relaxed text-white/70 max-w-xl">
                        Sistem pengurusan permohonan bantuan zakat yang cekap dan telus untuk
                        <span class="text-white/90 font-medium">Majlis Agama Islam Johor</span> — lengkap dengan
                        penilaian Had Kifayah automatik, analisis kelayakan asnaf berkuasa AI, dan pemantauan status
                        permohonan dalam satu platform bersepadu.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient">
                            <x-icon name="hand-heart" :size="19" /> Mohon Bantuan Zakat
                        </a>
                        <a href="{{ route('user.login') }}" class="btn btn-lg btn-glass">
                            <x-icon name="log-in" :size="19" /> Log Masuk
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-[12.5px] text-white/55">
                        <span class="inline-flex items-center gap-2"><x-icon name="shield-check" :size="15" class="text-emerald" /> Selamat &amp; disulitkan</span>
                        <span class="inline-flex items-center gap-2"><x-icon name="activity" :size="15" class="text-cyan" /> Penilaian automatik</span>
                        <span class="inline-flex items-center gap-2"><x-icon name="eye" :size="15" class="text-cyan" /> Telus &amp; boleh diaudit</span>
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
                                        <p class="text-[13px] font-semibold text-white">Penilaian AI</p>
                                        <p class="text-[11px] text-white/50">Analisis kelayakan asnaf</p>
                                    </div>
                                </div>
                                <span class="badge badge-success"><span class="w-1.5 h-1.5 rounded-full bg-success"></span> Aktif</span>
                            </div>

                            @php $total = max((int)($stats['total'] ?? 0), 0); @endphp
                            <div class="space-y-3">
                                @php
                                    $heroRows = [
                                        ['label' => 'Layak', 'value' => (int)($stats['layak'] ?? 0), 'color' => 'var(--color-success)', 'icon' => 'circle-check'],
                                        ['label' => 'Tidak Layak', 'value' => (int)($stats['tidak_layak'] ?? 0), 'color' => 'var(--color-danger)', 'icon' => 'circle-x'],
                                        ['label' => 'Dalam Semakan', 'value' => (int)($stats['dalam_semakan'] ?? 0), 'color' => 'var(--color-warning)', 'icon' => 'clock'],
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
                                <span class="text-[12px] text-white/50">Jumlah permohonan</span>
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
                <p class="text-eyebrow text-cyan mb-3">Perkhidmatan</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">Teknologi pintar untuk pengurusan zakat yang telus</h2>
                <p class="mt-4 text-[15px] text-white/60">Menggabungkan penilaian automatik, kecerdasan buatan dan aliran kerja digital untuk mempercepat bantuan sampai kepada yang layak.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12 stagger">
                @php
                    $features = [
                        ['icon' => 'brain', 'title' => 'Penilaian AI', 'desc' => 'Analisis kelayakan asnaf berkuasa AI dengan skor keyakinan dan justifikasi automatik.'],
                        ['icon' => 'calculator', 'title' => 'Kiraan Had Kifayah', 'desc' => 'Pengiraan Had Al-Kifayah mengikut metodologi MAIDAM dengan pecahan kategori lengkap.'],
                        ['icon' => 'file-plus', 'title' => 'Permohonan Online', 'desc' => 'Hantar permohonan bantuan zakat dalam talian, termasuk imbasan Borang Bantuan MAIJ.'],
                        ['icon' => 'chart-line', 'title' => 'Penjejakan Permohonan', 'desc' => 'Pantau status permohonan, dokumen, surat kelulusan dan resit dalam satu portal.'],
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
                <p class="text-eyebrow text-cyan mb-3">Statistik</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">Pemantauan permohonan secara langsung</h2>
                <p class="mt-4 text-[15px] text-white/60">Data dikemas kini daripada pangkalan data sistem — menunjukkan status semasa permohonan asnaf yang direkodkan.</p>
            </div>

            @php
                $statCards = [
                    ['label' => 'Jumlah Permohonan', 'value' => (int)($stats['total'] ?? 0), 'icon' => 'clipboard-list', 'color' => 'var(--color-cyan)'],
                    ['label' => 'Layak', 'value' => (int)($stats['layak'] ?? 0), 'icon' => 'circle-check', 'color' => 'var(--color-success)'],
                    ['label' => 'Tidak Layak', 'value' => (int)($stats['tidak_layak'] ?? 0), 'icon' => 'circle-x', 'color' => 'var(--color-danger)'],
                    ['label' => 'Dalam Semakan', 'value' => (int)($stats['dalam_semakan'] ?? 0), 'icon' => 'clock', 'color' => 'var(--color-warning)'],
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
                        <h3 class="text-[15px] font-semibold text-white">Trend Permohonan</h3>
                        <p class="text-[12px] text-white/50 mt-1">12 bulan terakhir</p>
                    </div>
                    <span class="badge badge-info"><x-icon name="activity" :size="13" /> Langsung</span>
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
                <p class="text-eyebrow text-cyan mb-3">Cara Memohon</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">Empat langkah mudah untuk memohon bantuan zakat</h2>
            </div>

            @php
                $steps = [
                    ['title' => 'Daftar akaun pemohon', 'desc' => 'Cipta akaun portal menggunakan nombor kad pengenalan dan maklumat peribadi anda.'],
                    ['title' => 'Lengkapkan permohonan', 'desc' => 'Isi borang permohonan dalam talian atau imbas Borang Bantuan MAIJ yang telah lengkap diisi.'],
                    ['title' => 'Semakan & pengesahan', 'desc' => 'Pegawai MAIJ menyemak permohonan, kelayakan Had Kifayah dan dokumen sokongan.'],
                    ['title' => 'Terima keputusan', 'desc' => 'Pantau status, muat turun surat keputusan dan resit permohonan melalui portal.'],
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
                <a href="{{ route('user.register') }}" class="btn btn-lg btn-gradient"><x-icon name="hand-heart" :size="19" /> Mulakan Permohonan</a>
                <a href="#faq" class="btn btn-lg btn-glass"><x-icon name="circle-help" :size="19" /> Soalan Lazim</a>
            </div>
        </div>
    </section>

    {{-- ============================== FAQ ============================== --}}
    <section id="faq" class="py-20 md:py-24" style="background: linear-gradient(180deg, #0B1220 0%, #0E1830 100%);">
        <div class="max-w-3xl mx-auto px-5 md:px-8">
            <div class="reveal text-center">
                <p class="text-eyebrow text-cyan mb-3">Soalan Lazim</p>
                <h2 class="text-[28px] sm:text-[34px] font-bold tracking-tight text-white leading-tight">Pertanyaan yang kerap diajukan</h2>
            </div>

            @php
                $faqs = [
                    ['q' => 'Siapa yang layak memohon bantuan zakat?', 'a' => 'Warganegara Malaysia beragama Islam yang bermastautin di Johor, dengan pendapatan isi rumah di bawah garis Had Kifayah yang ditetapkan.'],
                    ['q' => 'Apakah dokumen yang diperlukan?', 'a' => 'Salinan kad pengenalan, slip gaji atau penyata pendapatan, bil utiliti/komitmen bulanan, serta dokumen sokongan mengikut jenis bantuan.'],
                    ['q' => 'Bagaimanakah Had Kifayah dikira?', 'a' => 'Berdasarkan kategori isi rumah (ketua keluarga, pasangan, tanggungan, pelajar IPT dan kanak-kanak), status perumahan serta perbelanjaan tetap seperti utiliti, pengangkutan dan perubatan.'],
                    ['q' => 'Berapa lama tempoh semakan permohonan?', 'a' => 'Permohonan yang lengkap akan disemak oleh pegawai MAIJ mengikut tempoh yang ditetapkan. Anda boleh memantau status melalui portal pada bila-bila masa.'],
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
                        <h2 class="text-[26px] sm:text-[30px] font-bold tracking-tight text-white leading-tight">Mulakan permohonan bantuan zakat anda hari ini</h2>
                        <p class="mt-3 text-[14.5px] text-white/70">Daftar akaun pemohon dan lengkapkan permohonan dalam beberapa minit.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                        <a href="{{ route('user.register') }}" class="btn btn-lg bg-white text-navy hover:bg-white/90"><x-icon name="hand-heart" :size="19" /> Daftar Sekarang</a>
                        <a href="{{ route('user.login') }}" class="btn btn-lg btn-glass"><x-icon name="log-in" :size="19" /> Log Masuk</a>
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
                            <p class="text-[10.5px] text-white/45 mt-1 tracking-wider uppercase">Platform Pintar Pengurusan Zakat</p>
                        </div>
                    </div>
                    <p class="mt-5 text-[13.5px] leading-relaxed text-white/50 max-w-md">
                        Sistem pengurusan kelayakan asnaf pintar untuk Majlis Agama Islam Johor — menggabungkan penilaian Had Kifayah, analisis AI dan aliran kerja digital untuk mempercepat bantuan.
                    </p>
                    <div class="mt-6 flex items-start gap-3 text-[13px] text-white/55">
                        <x-icon name="map-pin" :size="17" class="text-cyan shrink-0 mt-0.5" />
                        <span>Majlis Agama Islam Johor, Pusat Islam Iskandar Johor,<br>Jalan Masjid Abu Bakar, 80990 Johor Bahru.</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2.5 text-[13px] text-white/55">
                        <x-icon name="phone" :size="16" class="text-cyan" />
                        <span>07-228 2928</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-[12px] font-semibold uppercase tracking-wider text-white/40 mb-4">Pautan Pantas</h4>
                    <ul class="space-y-2.5 text-[13.5px]">
                        <li><a href="#ciri" class="landing-section-link">Perkhidmatan</a></li>
                        <li><a href="#cara" class="landing-section-link">Cara Mohon</a></li>
                        <li><a href="#faq" class="landing-section-link">Soalan Lazim</a></li>
                        <li><a href="{{ route('user.hadkifayah') }}" class="landing-section-link">Kira Had Kifayah</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[12px] font-semibold uppercase tracking-wider text-white/40 mb-4">Akaun</h4>
                    <ul class="space-y-2.5 text-[13.5px]">
                        <li><a href="{{ route('user.register') }}" class="landing-section-link">Daftar Pemohon</a></li>
                        <li><a href="{{ route('user.login') }}" class="landing-section-link">Log Masuk Pemohon</a></li>
                        <li><a href="{{ route('login') }}" class="landing-section-link">Log Masuk Pentadbir</a></li>
                    </ul>
                </div>
            </div>

            <div class="landing-divider my-8"></div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-[12.5px] text-white/40">
                <p>&copy; {{ date('Y') }} J-ZAKAT · MAH. Hak cipta terpelihara.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-white/70 transition-fast">Privacy</a>
                    <a href="#" class="hover:text-white/70 transition-fast">Terms</a>
                    <a href="#" class="hover:text-white/70 transition-fast">About</a>
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
