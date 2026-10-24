<x-section-header title="Analisis AI Isi Rumah" icon="fa-brain" description="Penilaian pintar berdasarkan data isi rumah" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="lg:col-span-1 surface p-5 flex flex-col items-center justify-center text-center">
        <x-gauge-ring :percent="$aiAnalysis['score']" :size="130" :stroke="9"
            color="{{ $aiAnalysis['score'] >= 75 ? 'success' : ($aiAnalysis['score'] >= 50 ? 'warning' : 'danger') }}"
            label="Skor AI" sublabel="{{ $aiAnalysis['recommendation'] }}" />
        <p class="text-small text-tertiary mt-3">Keyakinan: <span class="text-text font-semibold">{{ $aiAnalysis['confidence'] }}%</span></p>
    </div>

    <div class="lg:col-span-2 space-y-1.5">
        @foreach($aiAnalysis['reasons'] as $reason)
            @php
                $isPos = str_starts_with($reason, '✓');
                $isNeg = str_starts_with($reason, '✗');
                $tone = $isPos ? 'success' : ($isNeg ? 'danger' : 'info');
                $icon = $isPos ? 'fa-circle-check' : ($isNeg ? 'fa-circle-xmark' : 'fa-circle');
            @endphp
            <x-insight-card tone="{{ $tone }}" icon="{{ $icon }}" title="{{ substr($reason, 2) }}" />
        @endforeach
    </div>
</div>

<x-app-card>
    <div class="space-y-3">
        @foreach($aiAnalysis['eligibility_checks'] as $check)
            <div class="flex items-center gap-3 p-2.5 rounded-[8px] {{ $check['passed'] ? 'bg-success-soft/40' : 'bg-danger-soft/40' }}">
                <div class="w-7 h-7 rounded-full {{ $check['passed'] ? 'bg-success text-white' : 'bg-danger text-white' }} flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $check['passed'] ? 'fa-check' : 'fa-xmark' }} text-[11px]"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-small font-semibold text-text">{{ $check['label'] }}</p>
                    <p class="text-[11px] text-secondary mt-0.5">{{ $check['detail'] }}</p>
                </div>
                <x-status-badge :status="$check['passed'] ? 'success' : 'danger'" size="sm" :dot="true">
                    {{ $check['passed'] ? 'Lulus' : 'Gagal' }}
                </x-status-badge>
            </div>
        @endforeach
    </div>
</x-app-card>

<div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="surface p-3.5 text-center">
        <p class="text-[11px] uppercase tracking-wide text-tertiary">Had Kifayah</p>
        <p class="text-metric text-text mt-0.5">RM {{ number_format($aiAnalysis['had_kifayah_total'], 0) }}</p>
    </div>
    <div class="surface p-3.5 text-center">
        <p class="text-[11px] uppercase tracking-wide text-tertiary">Pendapatan</p>
        <p class="text-metric text-text mt-0.5">RM {{ number_format($household->total_income, 0) }}</p>
    </div>
    <div class="surface p-3.5 text-center">
        <p class="text-[11px] uppercase tracking-wide text-tertiary">Jurang</p>
        <p class="text-metric {{ $aiAnalysis['income_gap'] > 0 ? 'text-danger' : 'text-success' }} mt-0.5">
            RM {{ number_format(abs($aiAnalysis['income_gap']), 0) }}
        </p>
    </div>
    <div class="surface p-3.5 text-center">
        <p class="text-[11px] uppercase tracking-wide text-tertiary">Syor</p>
        <p class="text-metric text-text mt-0.5">{{ round($aiAnalysis['score']) }}%</p>
    </div>
</div>
