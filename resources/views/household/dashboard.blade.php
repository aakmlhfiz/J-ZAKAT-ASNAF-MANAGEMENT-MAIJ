@extends('layouts.app')

@section('title', 'Household Intelligence - J-ZAKAT')

@php
    $title = 'Household Intelligence';
    $subtitle = 'Analisis & pengurusan isi rumah secara menyeluruh';
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('householdDashboard', () => ({
            ic: '',
            results: [],
            count: 0,
            searchQuery: '',
            loading: false,
            noResults: false,
            timer: null,
            filtersOpen: false,

            stats: @json($totals),
            statsRefreshing: false,
            lastUpdated: new Date(),
            pollInterval: null,

            init() {
                this.pollInterval = setInterval(() => this.refreshStats(), 30000);
            },

            destroy() {
                if (this.pollInterval) clearInterval(this.pollInterval);
            },

            async search() {
                if (this.ic.length < 2) {
                    this.results = []; this.count = 0; this.noResults = false; this.searchQuery = '';
                    return;
                }
                this.noResults = false;
                clearTimeout(this.timer);
                this.timer = setTimeout(async () => {
                    this.loading = true;
                    try {
                        const res = await fetch(`/api/household/search?q=${encodeURIComponent(this.ic)}`);
                        const data = await res.json();
                        this.results = data.results ?? [];
                        this.count = data.count ?? 0;
                        this.searchQuery = data.query ?? '';
                        this.noResults = this.results.length === 0;
                    } catch(e) { this.results = []; this.count = 0; this.noResults = true; }
                    this.loading = false;
                }, 200);
            },

            clearSearch() {
                this.ic = '';
                this.results = [];
                this.count = 0;
                this.noResults = false;
                this.searchQuery = '';
                this.loading = false;
            },

            submitSearch() {
                if (this.ic.length < 2) return;
                window.location.href = `{{ route('household.search') }}?ic=${encodeURIComponent(this.ic)}`;
            },

            initials(name) {
                return (name || 'N/A').split(/\s+/).map(w => w.charAt(0).toUpperCase()).slice(0, 2).join('');
            },

            rankTone(status) {
                if (!status) return 'tertiary';
                const s = status.toLowerCase();
                if (s === 'layak') return 'success';
                if (s === 'tidak layak' || s === 'tidak-layak') return 'danger';
                return 'warning';
            },

            async refreshStats() {
                this.statsRefreshing = true;
                try {
                    const res = await fetch('/app/api/household/stats');
                    if (res.ok) {
                        const data = await res.json();
                        this.stats = data;
                        this.lastUpdated = new Date();
                    }
                } catch(e) { /* ignore */ }
                this.statsRefreshing = false;
            }
        }));
    });
</script>
@endpush

@section('content')
<x-page-header title="Household Intelligence" subtitle="Analisis isi rumah & pengurusan bantuan secara menyeluruh" icon="fa-house-chimney-user" eyebrow="Isi Rumah" />

<div x-data="householdDashboard">

    <div class="flex items-center justify-between mb-3">
        <span class="text-eyebrow text-tertiary uppercase tracking-wider">Carian Pantas Penerima</span>
        <span class="inline-flex items-center gap-1.5 text-[11px] text-tertiary" title="Data dikemas kini setiap 30 saat">
            <i class="fa-solid text-[11px]" :class="statsRefreshing ? 'fa-spinner fa-spin text-warning' : 'fa-circle-check text-success'"></i>
            <span x-text="statsRefreshing ? 'Mengemas kini...' : 'Kemas Kini Auto'"></span>
        </span>
    </div>

    <div class="surface p-4 mb-6 space-y-3">
        <div class="flex items-center gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-primary text-[15px] pointer-events-none"></i>
                <input type="text" x-model="ic" @input="search()" @keydown.enter="submitSearch()"
                    placeholder="CARI NAMA ATAU NO. IC PENERIMA..."
                    maxlength="12" autocomplete="off"
                    class="input-base pl-10 pr-10 py-2.5 text-[14px] w-full placeholder:uppercase placeholder:tracking-[0.04em]">
                <button type="button" x-cloak x-show="ic.length > 0" @click="clearSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-tertiary hover:text-text transition-fast p-1"
                    aria-label="Kosongkan">
                    <i class="fa-solid fa-xmark text-[13px]"></i>
                </button>
            </div>
            <button @click="submitSearch()" class="btn btn-search-dark min-w-[72px]" :disabled="ic.length < 2 || loading">
                <template x-if="loading"><i class="fa-solid fa-spinner fa-spin text-[11px]"></i></template>
                <span>CARI</span>
            </button>
        </div>

        <template x-if="results.length > 0 && ic.length >= 2">
            <div class="pt-2 border-t border-divider">
                <p class="text-eyebrow text-tertiary mb-2"><span x-text="count"></span> hasil carian untuk "<span class="text-text font-medium" x-text="searchQuery"></span>"</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <template x-for="r in results" :key="r.household_id">
                        <a :href="r.url" class="flex items-center gap-3 p-3 rounded-[10px] bg-muted hover:bg-primary/5 transition-apple group cursor-pointer">
                            <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center text-[12px] font-bold"
                                :class="{
                                    'bg-success/10 text-success': r.eligibility_status === 'Layak',
                                    'bg-danger/10 text-danger': r.eligibility_status === 'Tidak Layak',
                                    'bg-warning/10 text-warning': !r.eligibility_status || (r.eligibility_status !== 'Layak' && r.eligibility_status !== 'Tidak Layak'),
                                }"
                                x-text="initials(r.name)">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-[13px] font-semibold text-text truncate" x-text="r.name"></p>
                                    <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-[6px] text-[10px] font-semibold bg-primary/10 text-primary" x-text="r.hubungan"></span>
                                </div>
                                <p class="text-[11px] text-tertiary mt-0.5 truncate">
                                    <span x-text="r.ic"></span> &middot; <span x-text="r.household_name"></span>
                                </p>
                                <div class="flex items-center gap-3 mt-1">
                                    <template x-if="r.daerah">
                                        <span class="text-[10px] text-tertiary inline-flex items-center gap-1">
                                            <i class="fa-solid fa-location-dot text-[9px]"></i> <span x-text="r.daerah"></span>
                                        </span>
                                    </template>
                                    <template x-if="r.eligibility_status">
                                        <span class="text-[10px] font-semibold" :class="{
                                            'text-success': r.eligibility_status === 'Layak',
                                            'text-danger': r.eligibility_status === 'Tidak Layak',
                                            'text-warning': r.eligibility_status !== 'Layak' && r.eligibility_status !== 'Tidak Layak',
                                        }" x-text="r.eligibility_status"></span>
                                    </template>
                                    <span class="text-[10px] text-tertiary" x-text="r.members_count + ' ahli'"></span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-[10px] text-tertiary group-hover:translate-x-0.5 transition-fast shrink-0"></i>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="noResults && ic.length >= 2 && !loading">
            <div class="pt-2 border-t border-divider">
                <div class="flex items-center gap-3 p-4 rounded-[10px] bg-muted text-small text-secondary">
                    <i class="fa-solid fa-circle-exclamation text-[16px] text-warning shrink-0"></i>
                    <span>Tiada hasil untuk "<strong class="text-text" x-text="searchQuery"></strong>". Cuba istilah lain.</span>
                </div>
            </div>
        </template>

        <template x-if="loading && ic.length >= 2">
            <div class="pt-2 border-t border-divider">
                <div class="flex items-center gap-3 p-3 text-small text-tertiary">
                    <i class="fa-solid fa-spinner fa-spin text-[13px]"></i> Mencari...
                </div>
            </div>
        </template>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-6">
        <x-stat-card label="Isi Rumah" xValue="stats.total_households" icon="fa-home" accent="primary" iconBg="bg-primary/10" iconColor="text-primary" />
        <x-stat-card label="Jumlah Ahli" xValue="stats.total_members" icon="fa-users" accent="info" iconBg="bg-info/10" iconColor="text-info" />
        <x-stat-card label="Bantuan" xValue="'RM ' + stats.total_assistance" icon="fa-hand-holding-heart" accent="success" iconBg="bg-success/10" iconColor="text-success" />
        <x-stat-card label="Aktif" xValue="stats.active_assistance" icon="fa-circle-check" accent="success" iconBg="bg-success/10" iconColor="text-success" />
        <x-stat-card label="Layak" xValue="stats.eligible" icon="fa-thumbs-up" accent="warning" iconBg="bg-warning/10" iconColor="text-warning" />
        <x-stat-card label="AI Tinggi" xValue="stats.high_ai" icon="fa-brain" accent="info" iconBg="bg-info/10" iconColor="text-info" />
    </div>

    <div class="surface">
        <button @click="filtersOpen = !filtersOpen" class="w-full flex items-center justify-between p-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-filter text-tertiary text-[13px]"></i>
                <span class="text-body font-semibold text-text">Penapis Pantas</span>
                @if($filter)
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-primary-soft text-primary">{{ $filter }}</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($filter)
                    <a href="{{ route('household.dashboard') }}" class="text-[12px] text-secondary hover:text-danger transition-fast">
                        <i class="fa-solid fa-xmark mr-1"></i> Kosongkan
                    </a>
                @endif
                <i class="fa-solid text-[12px] text-tertiary transition-fast" :class="filtersOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
        </button>
        <div x-show="filtersOpen" x-cloak class="px-4 pb-4 border-t border-divider">
            <div class="flex flex-wrap gap-1.5 pt-4">
                @php
                    $filters = [
                        'rental' => ['label' => 'Rumah Sewa', 'icon' => 'fa-house'],
                        'dialysis' => ['label' => 'Dialisis', 'icon' => 'fa-heart-pulse'],
                        'single_mother' => ['label' => 'Ibu Tunggal', 'icon' => 'fa-person'],
                        'ipt' => ['label' => 'IPT', 'icon' => 'fa-graduation-cap'],
                        'oku' => ['label' => 'OKU', 'icon' => 'fa-wheelchair'],
                        'elderly' => ['label' => 'Warga Emas', 'icon' => 'fa-clock'],
                        'high_ai' => ['label' => 'AI Tinggi', 'icon' => 'fa-brain'],
                        'eligible' => ['label' => 'Layak', 'icon' => 'fa-check-circle'],
                        'approved' => ['label' => 'Diluluskan', 'icon' => 'fa-circle-check'],
                        'rejected' => ['label' => 'Ditolak', 'icon' => 'fa-circle-xmark'],
                    ];
                @endphp
                @foreach($filters as $key => $f)
                    <a href="{{ route('household.dashboard', ['filter' => $key]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-[12px] font-semibold transition-apple {{ $filter === $key ? 'bg-primary text-white shadow-sm' : 'bg-muted text-secondary hover:bg-primary/10 hover:text-primary' }}">
                        <i class="fa-solid {{ $f['icon'] }} text-[10px]"></i> {{ $f['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-8 mb-4">
        <x-section-header title="Senarai Isi Rumah" icon="fa-list" class="mb-0!" />
        <span class="text-small text-tertiary">{{ $households->total() }} isi rumah</span>
    </div>

    @if($households->isEmpty())
        <x-empty-state icon="fa-house-chimney-user" title="Tiada Isi Rumah" description="Belum ada data isi rumah." />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($households as $h)
                @php $ketua = $h->ketua; @endphp
                <a href="{{ route('household.show', $h->household_id) }}" class="surface surface-hover p-5 block group transition-apple">
                    <div class="flex items-start justify-between mb-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-body font-semibold text-text truncate">{{ $ketua?->nama ?? 'N/A' }}</p>
                            <p class="text-eyebrow text-tertiary mt-0.5">{{ $h->household_id }}</p>
                        </div>
                        <div class="shrink-0">
                            <x-gauge-ring :percent="round($h->ai_score)" :size="48" :stroke="4" color="{{ $h->ai_score >= 75 ? 'success' : ($h->ai_score >= 50 ? 'warning' : 'danger') }}" />
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <x-status-badge :status="strtolower(str_replace(' ', '-', $h->eligibility_status))" size="sm" />
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-pill bg-muted text-secondary">
                            <i class="fa-solid fa-users text-[9px]"></i> {{ $h->members->count() }}
                        </span>
                        @if($h->daerah)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-pill bg-muted text-secondary truncate max-w-[160px]">
                                <i class="fa-solid fa-location-dot text-[9px]"></i> {{ $h->daerah }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-small text-secondary">
                        <span>RM {{ number_format($h->total_income, 0) }}/bulan</span>
                        <span>{{ $h->active_assistance_count }} bantuan</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-divider flex items-center justify-end">
                        <span class="text-[12px] font-semibold text-primary group-hover:translate-x-0.5 transition-fast">
                            Lihat Profil <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        @if($households->hasPages())
            <div class="mt-8">{{ $households->links() }}</div>
        @endif
    @endif
</div>
@endsection
