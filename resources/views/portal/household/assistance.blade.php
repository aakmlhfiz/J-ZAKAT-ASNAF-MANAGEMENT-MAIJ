@extends('portal.layouts.app')
@section('title', 'Bantuan Saya — Portal Pemohon')
@section('page-title', 'Bantuan Saya')

@section('content')
<x-page-header
    title="Bantuan Saya"
    subtitle="Semua bantuan yang diterima oleh ahli isi rumah anda."
/>

@if(!$household)
    <x-app-card>
        <x-empty-state icon="fa-hand-holding-heart" title="Tiada rekod bantuan" description="Bantuan yang diterima oleh isi rumah anda akan dipaparkan di sini selepas kelulusan MAIJ." />
    </x-app-card>
@else
    {{-- Totals --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 stagger">
        <x-stat-card label="Bantuan Bulanan" :value="'RM '.number_format($totals['monthly'], 2)" icon="fa-calendar-check" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
        <x-stat-card label="Bantuan One-Off" :value="'RM '.number_format($totals['one_off'], 2)" icon="fa-gift" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
        <x-stat-card label="Jumlah Keseluruhan" :value="'RM '.number_format($totals['total'], 2)" icon="fa-coins" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    </div>

    @forelse($members as $member)
        @php $items = $assistanceByMember[$member->id] ?? collect(); @endphp
        @if($items->isNotEmpty())
        <x-app-card class="p-0 mb-5" :hover="false">
            <div class="px-6 pt-5 pb-3 border-b border-divider flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center font-semibold">
                    {{ strtoupper(mb_substr($member->nama, 0, 1)) }}
                </div>
                <div>
                    <p class="text-body text-text font-semibold">{{ strtoupper($member->nama) }}</p>
                    <p class="text-eyebrow text-tertiary">{{ $member->hubungan }}</p>
                </div>
            </div>
            <div class="divide-y divide-divider">
                @foreach($items as $item)
                <div class="px-6 py-4 flex items-start justify-between gap-4 flex-wrap">
                    <div class="min-w-0">
                        <p class="text-body text-text font-medium">{{ $item->jenis_bantuan }}</p>
                        <p class="text-small text-secondary mt-0.5">
                            RM {{ number_format((float) $item->amount, 2) }}
                            {{ $item->frequency === 'monthly' ? '/ bulan' : '' }}
                        </p>
                        @if($item->reference_number)
                            <p class="text-eyebrow text-tertiary mt-1">No. Rujukan: {{ $item->reference_number }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <x-status-badge status="success">Aktif</x-status-badge>
                        @if($item->approved_date)
                            <p class="text-eyebrow text-tertiary mt-1">{{ $item->approved_date->format('d M Y') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </x-app-card>
        @endif
    @empty
        <x-app-card>
            <x-empty-state icon="fa-hand-holding-heart" title="Tiada bantuan direkodkan" description="Bantuan yang diluluskan akan dipaparkan di sini." />
        </x-app-card>
    @endforelse

    @if($members->isNotEmpty() && $assistanceByMember->isEmpty())
        <x-app-card>
            <x-empty-state icon="fa-hand-holding-heart" title="Tiada bantuan direkodkan" description="Bantuan yang diluluskan untuk isi rumah anda akan dipaparkan di sini." />
        </x-app-card>
    @endif
@endif
@endsection
