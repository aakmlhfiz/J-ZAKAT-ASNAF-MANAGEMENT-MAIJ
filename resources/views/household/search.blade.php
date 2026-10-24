@extends('layouts.app')

@section('title', 'Carian Isi Rumah - Household Intelligence - J-ZAKAT')

@php
    $title = 'Carian Isi Rumah';
    $subtitle = 'Cari isi rumah melalui IC, nama, daerah atau ID';
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('icSearch', () => ({
            ic: '{{ $ic ?? '' }}',
            results: [],
            count: 0,
            searchQuery: '',
            loading: false,
            noResults: false,
            timer: null,

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
            }
        }));
    });
</script>
@endpush

@section('content')
<x-page-header title="Carian Isi Rumah" subtitle="Cari isi rumah melalui IC, nama, daerah atau ID" icon="fa-magnifying-glass" eyebrow="Isi Rumah" />

<div class="max-w-2xl">
    <div x-data="icSearch" class="space-y-6">
        <div class="surface p-6">
            <form method="GET" action="{{ route('household.search') }}" @submit.prevent="submitSearch()" class="space-y-4">
                <div>
                    <label for="ic" class="block text-body font-semibold text-text mb-2">Cari Isi Rumah</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-primary text-[15px]"></i>
                        <input type="text" id="ic" name="ic" x-model="ic" @input="search()"
                            value="{{ $ic ?? '' }}" placeholder="CARI NAMA ATAU NO. IC PENERIMA..."
                            maxlength="12"
                            class="input-base pl-12 pr-10 py-3.5 text-[15px] w-full placeholder:uppercase placeholder:tracking-[0.04em]"
                            autocomplete="off" autofocus>
                        <button type="button" x-cloak x-show="ic.length > 0" @click="clearSearch()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-tertiary hover:text-text transition-fast p-1"
                            aria-label="Kosongkan">
                            <i class="fa-solid fa-xmark text-[13px]"></i>
                        </button>
                    </div>
                    <p class="text-small text-tertiary mt-2">Cari melalui: IC, nama, hubungan (Ketua / Isteri / Anak), daerah, mukim, atau ID isi rumah.</p>
                </div>

                <button type="submit" class="btn btn-search-dark" :disabled="ic.length < 2 || loading">
                    <template x-if="loading"><i class="fa-solid fa-spinner fa-spin text-[11px]"></i></template>
                    <span>CARI</span>
                </button>
            </form>

            <template x-if="results.length > 0 && ic.length >= 2">
                <div class="mt-4 pt-4 border-t border-divider">
                    <p class="text-eyebrow text-tertiary mb-3"><span x-text="count"></span> hasil carian untuk "<span class="text-text font-medium" x-text="searchQuery"></span>"</p>
                    <div class="space-y-2">
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
                <div class="mt-4 pt-4 border-t border-divider">
                    <div class="flex items-center gap-3 p-4 rounded-[10px] bg-muted text-small text-secondary">
                        <i class="fa-solid fa-circle-exclamation text-[16px] text-warning shrink-0"></i>
                        <span>Tiada hasil untuk "<strong class="text-text" x-text="searchQuery"></strong>". Cuba istilah lain.</span>
                    </div>
                </div>
            </template>

            <template x-if="loading && ic.length >= 2">
                <div class="mt-4 pt-4 border-t border-divider flex items-center gap-3 text-small text-tertiary">
                    <i class="fa-solid fa-spinner fa-spin text-[13px]"></i> Mencari...
                </div>
            </template>
        </div>
    </div>
</div>

@if(isset($result) && $result)
    @php
        $household = $result['household'];
        $matched = $result['matched_member'];
        $ketua = $household->ketua;
    @endphp
    <div class="mt-8">
        <x-section-header title="Hasil Carian - Isi Rumah {{ $ketua?->nama }}"
            description="IC {{ $matched->ic }} ditemui sebagai <strong>{{ $matched->hubungan }}</strong> dalam isi rumah ini."
            icon="fa-circle-check" />

        <a href="{{ route('household.show', $household->household_id) }}" class="surface surface-hover p-6 block group transition-apple">
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-house-chimney-user text-[20px]"></i>
                        </div>
                        <div>
                            <p class="text-h3 text-text font-semibold">{{ $ketua?->nama ?? 'N/A' }}</p>
                            <p class="text-body text-primary font-semibold">{{ $household->household_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mt-4">
                        <div>
                            <p class="text-eyebrow text-tertiary">Ahli Isi Rumah</p>
                            <p class="text-metric text-text mt-1">{{ $household->members->count() }} <span class="text-body text-secondary">orang</span></p>
                        </div>
                        <div>
                            <p class="text-eyebrow text-tertiary">Pendapatan</p>
                            <p class="text-metric text-text mt-1">RM {{ number_format($household->total_income, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-eyebrow text-tertiary">Perbelanjaan</p>
                            <p class="text-metric text-text mt-1">RM {{ number_format($household->total_expenses, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-eyebrow text-tertiary">Skor AI</p>
                            <p class="text-metric text-text mt-1">{{ round($household->ai_score) }}%</p>
                        </div>
                        <div>
                            <p class="text-eyebrow text-tertiary">Status</p>
                            <div class="mt-1"><x-status-badge :status="strtolower(str_replace(' ', '-', $household->eligibility_status))" /></div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-small text-secondary">IC dipadankan:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] bg-success-soft text-success text-[12px] font-semibold">
                            <i class="fa-solid fa-check-circle text-[10px]"></i> {{ $matched->nama }} ({{ $matched->hubungan }})
                        </span>
                    </div>
                </div>

                <div class="shrink-0 flex flex-col items-center gap-2">
                    <x-gauge-ring :percent="round($household->ai_score)" :size="100" :stroke="8"
                        color="{{ $household->ai_score >= 75 ? 'success' : ($household->ai_score >= 50 ? 'warning' : 'danger') }}"
                        label="AI" />
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-divider flex items-center justify-between">
                <span class="text-small text-secondary">
                    <i class="fa-solid fa-location-dot text-[11px] mr-1"></i>
                    {{ $household->alamat ?? 'Alamat tidak direkodkan' }}
                </span>
                <span class="text-[13px] font-semibold text-primary group-hover:translate-x-0.5 transition-fast">
                    Lihat Profil Penuh <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                </span>
            </div>
        </a>
    </div>
@elseif(request()->has('ic') && !$result)
    <div class="mt-8">
        <x-empty-state icon="fa-circle-exclamation" title="Tiada Hasil" description="Tiada isi rumah ditemui untuk IC tersebut. Sila semak semula nombor IC." />
    </div>
@endif
@endsection
