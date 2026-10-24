@extends('portal.layouts.app')
@section('title', 'Kewangan Saya — Portal Pemohon')
@section('page-title', 'Kewangan Saya')

@section('content')
<x-page-header
    title="Kewangan Saya"
    subtitle="Ringkasan kewangan isi rumah anda."
/>

@if(!$household)
    <x-app-card>
        <x-empty-state icon="fa-wallet" title="Tiada maklumat kewangan" description="Maklumat kewangan isi rumah akan muncul selepas permohonan anda diproses." />
    </x-app-card>
@else
    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 stagger">
        <x-stat-card label="Jumlah Pendapatan Isi Rumah" :value="'RM '.number_format($summary['total_income'], 2)" icon="fa-money-bill-trend-up" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
        <x-stat-card label="Jumlah Komitmen" :value="'RM '.number_format($summary['total_expenses'], 2)" icon="fa-file-invoice-dollar" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
        <x-stat-card label="Bilangan Tanggungan" :value="$summary['total_tanggungan']" icon="fa-people-roof" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Income --}}
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Pendapatan" description="Sumber pendapatan isi rumah" icon="fa-money-bill-wave" />
            <dl class="space-y-3 text-body">
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">Pendapatan Pemohon</span><span class="text-text font-semibold">RM {{ number_format($income['applicant'], 2) }}</span></div>
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">Pendapatan Pasangan</span><span class="text-text font-semibold">RM {{ number_format($income['spouse'], 2) }}</span></div>
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">Pendapatan Ahli Lain</span><span class="text-text font-semibold">RM {{ number_format($income['other'], 2) }}</span></div>
                <div class="flex justify-between pt-2"><span class="text-text font-semibold">Jumlah Pendapatan</span><span class="text-text font-bold">RM {{ number_format($summary['total_income'], 2) }}</span></div>
            </dl>
        </x-app-card>

        {{-- Commitments --}}
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Komitmen Bulanan" description="Perbelanjaan tetap isi rumah" icon="fa-receipt" />
            <div class="py-8 text-center">
                <div class="w-14 h-14 rounded-full bg-warning-soft text-warning flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-invoice-dollar text-[22px]"></i>
                </div>
                <p class="text-metric-lg text-text">RM {{ number_format($summary['total_expenses'], 2) }}</p>
                <p class="text-small text-secondary mt-1">Jumlah komitmen bulanan direkodkan</p>
            </div>
        </x-app-card>
    </div>

    {{-- Net summary card --}}
    @php $net = $summary['total_income'] - $summary['total_expenses']; @endphp
    <x-app-card class="p-6 mt-6" :hover="false" :accent="$net >= 0 ? 'success' : 'danger'">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-eyebrow text-tertiary">Baki Pendapatan Selepas Komitmen</p>
                <h2 class="text-h3 text-text mt-1">RM {{ number_format($net, 2) }}</h2>
            </div>
            <x-status-badge :status="$net >= 0 ? 'success' : 'danger'">{{ $net >= 0 ? 'Lebihan' : 'Defisit' }}</x-status-badge>
        </div>
    </x-app-card>
@endif
@endsection
