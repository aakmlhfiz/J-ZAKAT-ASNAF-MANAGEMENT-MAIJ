@extends('layouts.app')

@section('title', 'Profil Isi Rumah - ' . ($household->ketua?->nama ?? 'N/A') . ' - J-ZAKAT')

@php
    $title = 'Profil Isi Rumah';
    $subtitle = $household->household_id . ' - ' . ($household->ketua?->nama ?? 'N/A');
    $ketua = $household->ketua;
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('householdProfile', () => ({
            activeSection: '{{ $activeTab }}',

            scrollTo(el) {
                const target = document.getElementById(el);
                if (!target) return;
                this.activeSection = el;
                const top = target.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        }));
    });
</script>
@endpush

@section('content')
<div x-data="householdProfile">

    <x-page-header title="{{ $ketua?->nama ?? 'N/A' }}" subtitle="{{ $household->household_id }} · {{ $household->daerah ?? '' }}" icon="fa-house-chimney-user" eyebrow="Profil Isi Rumah" />

    {{-- Hero + AI gauge --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
        <div class="lg:col-span-4 surface p-5 flex items-start gap-4">
            <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                <i class="fa-solid fa-house-chimney-user text-[22px]"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-h3 text-text font-semibold">{{ $ketua?->nama ?? 'N/A' }}</h2>
                <p class="text-body text-primary font-semibold mt-0.5">{{ $household->household_id }}</p>
                <p class="text-small text-secondary mt-1">
                    <i class="fa-solid fa-location-dot text-[11px] mr-1"></i>
                    {{ $household->alamat ?? 'Alamat tidak direkodkan' }}
                </p>
                <div class="flex flex-wrap gap-2 mt-3">
                    <x-status-badge :status="strtolower(str_replace(' ', '-', $household->eligibility_status))" />
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[12px] font-semibold rounded-pill bg-info-soft text-info">
                        <i class="fa-solid fa-users text-[10px]"></i> {{ $household->total_members }} Ahli
                    </span>
                    @if($household->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[12px] font-semibold rounded-pill bg-success-soft text-success">
                            <i class="fa-solid fa-circle-check text-[10px]"></i> Aktif
                        </span>
                    @endif
                    @if($household->total_monthly_assistance > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[12px] font-semibold rounded-pill bg-success-soft text-success">
                            RM {{ number_format($household->total_monthly_assistance, 0) }}/bln
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="surface p-5 flex flex-col items-center justify-center text-center">
            <x-gauge-ring :percent="round($household->ai_score)" :size="90" :stroke="7"
                color="{{ $household->ai_score >= 75 ? 'success' : ($household->ai_score >= 50 ? 'warning' : 'danger') }}"
                label="AI" />
        </div>
    </div>

    {{-- 4 key stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <x-stat-card label="Ahli" :value="$household->total_members" icon="fa-users" accent="primary" iconBg="bg-primary/10" iconColor="text-primary" />
        <x-stat-card label="Pendapatan" value="RM {{ number_format($household->total_income, 2) }}" icon="fa-money-bill-wave" accent="info" iconBg="bg-info/10" iconColor="text-info" />
        <x-stat-card label="Perbelanjaan" value="RM {{ number_format($household->total_expenses, 2) }}" icon="fa-receipt" accent="warning" iconBg="bg-warning/10" iconColor="text-warning" />
        <x-stat-card label="Bantuan" value="RM {{ number_format($household->total_assistance_received, 2) }}" icon="fa-hand-holding-heart" accent="success" iconBg="bg-success/10" iconColor="text-success" />
    </div>

    {{-- Duplicate warnings --}}
    @if(isset($duplicateCheck) && $duplicateCheck['has_warnings'])
        <div class="mb-6 space-y-2">
            @foreach($duplicateCheck['warnings'] as $warning)
                <x-insight-card
                    tone="{{ $warning['severity'] === 'high' ? 'danger' : ($warning['severity'] === 'medium' ? 'warning' : 'info') }}"
                    icon="{{ $warning['severity'] === 'high' ? 'fa-circle-exclamation' : ($warning['severity'] === 'medium' ? 'fa-triangle-exclamation' : 'fa-circle-info') }}"
                    title="{{ $warning['title'] }}"
                    description="{{ $warning['description'] }}" />
            @endforeach
        </div>
    @endif

    {{-- Insights --}}
    @if(isset($insights) && count($insights) > 0)
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach($insights as $insight)
                <x-insight-card tone="{{ $insight['tone'] }}" icon="{{ $insight['icon'] }}"
                    title="{{ $insight['title'] }}" description="{{ $insight['description'] }}" />
            @endforeach
        </div>
    @endif

    {{-- Sticky quick-jump nav --}}
    <div class="sticky top-0 z-30 bg-bg/95 backdrop-blur-sm pb-3 -mx-0 mb-6">
        <div class="flex flex-wrap gap-1">
            @php
                $sections = [
                    ['id' => 'section-members', 'label' => 'Ahli', 'icon' => 'fa-users'],
                    ['id' => 'section-matrix', 'label' => 'Matriks', 'icon' => 'fa-table'],
                    ['id' => 'section-history', 'label' => 'Sejarah', 'icon' => 'fa-clock-rotate-left'],
                    ['id' => 'section-ai', 'label' => 'AI', 'icon' => 'fa-brain'],
                    ['id' => 'section-timeline', 'label' => 'Garis Masa', 'icon' => 'fa-timeline'],
                ];
            @endphp
            @foreach($sections as $s)
                <button @click="scrollTo('{{ $s['id'] }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[8px] text-[13px] font-semibold transition-apple"
                    :class="activeSection === '{{ $s['id'] }}' ? 'bg-primary text-white shadow-sm' : 'text-secondary hover:text-text hover:bg-muted'">
                    <i class="fa-solid {{ $s['icon'] }} text-[11px]"></i> {{ $s['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Members --}}
    <div id="section-members" class="mb-10">
        @include('household.partials.members')
    </div>

    {{-- Benefit Matrix --}}
    <div id="section-matrix" class="mb-10">
        @include('household.partials.matrix')
    </div>

    {{-- History --}}
    <div id="section-history" class="mb-10">
        @include('household.partials.history')
    </div>

    {{-- AI Analysis --}}
    <div id="section-ai" class="mb-10">
        @include('household.partials.ai')
    </div>

    {{-- Timeline --}}
    <div id="section-timeline" class="mb-10">
        @include('household.partials.timeline')
    </div>

</div>
@endsection
