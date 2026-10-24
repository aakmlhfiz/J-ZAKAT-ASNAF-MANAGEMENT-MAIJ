@extends('layouts.app', [
    'title' => 'Laporan',
    'subtitle' => 'Ringkasan & analitik pemohon MAIJ',
])
@section('content')

<x-page-header title="Laporan" subtitle="Ringkasan & analitik pemohon MAIJ" eyebrow="Pengurusan Data" icon="fa-chart-column">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('reports.pdf') }}">Eksport PDF</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-file-excel" href="{{ route('reports.excel') }}">Eksport Excel</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 stagger">
    <x-stat-card label="Jumlah Permohonan" :value="number_format($totals['jumlah'])" icon="fa-database" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    <x-stat-card label="Layak" :value="number_format($totals['layak'])" icon="fa-circle-check" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    <x-stat-card label="Tidak Layak" :value="number_format($totals['tidak_layak'])" icon="fa-circle-xmark" icon-bg="bg-danger-soft" icon-color="text-danger" accent="danger" />
    <x-stat-card label="Dalam Semakan" :value="number_format($totals['semakan'])" icon="fa-clock" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">
    <x-app-card class="lg:col-span-2 p-6" :hover="false">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-eyebrow text-tertiary">Trend</p>
                <h2 class="text-h3 text-text mt-1">Permohonan Bulanan</h2>
                <p class="text-small text-secondary mt-1">Jumlah permohonan dalam 12 bulan terakhir</p>
            </div>
        </div>
        <x-trend-chart :data="$trendValues" :labels="$trendLabels" color="#3B82F6" height="280" />
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <x-section-header title="Status Perkhidmatan" description="Sambungan sistem semasa" icon="fa-server" />
        <div class="space-y-2.5">
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                <p class="text-body text-text flex-1">Pangkalan Data</p>
                <x-status-badge status="success">Aktif</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                <p class="text-body text-text flex-1">Firebase Auth</p>
                <x-status-badge status="success">Aktif</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                <p class="text-body text-text flex-1">Enjin AI</p>
                <x-status-badge status="primary">Aktif</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-warning"></span>
                <p class="text-body text-text flex-1">Cache &amp; Sesi</p>
                <x-status-badge status="warning">Penyelenggaraan</x-status-badge>
            </div>
        </div>
    </x-app-card>
</div>

<x-app-card padding="p-0" :hover="false" class="mt-6">
    <div class="p-6 pb-3 flex items-center justify-between">
        <div>
            <h2 class="text-h3 text-text">Ringkasan Bulanan</h2>
            <p class="text-small text-secondary mt-1">Data terperinci untuk setiap bulan</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Layak</th>
                    <th class="text-right">Tidak Layak</th>
                    <th class="text-right">Semakan</th>
                    <th class="text-right">% Layak</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthly as $m)
                    <tr>
                        <td class="font-medium text-text">{{ $m['bulan'] }}</td>
                        <td class="text-right tabular-nums">{{ $m['jumlah'] }}</td>
                        <td class="text-right tabular-nums text-success font-semibold">{{ $m['layak'] }}</td>
                        <td class="text-right tabular-nums text-danger font-semibold">{{ $m['tidak_layak'] ?? ($m['tidak'] ?? 0) }}</td>
                        <td class="text-right tabular-nums text-warning font-semibold">{{ $m['semakan'] }}</td>
                        <td class="text-right tabular-nums font-semibold">{{ $m['pct_layak'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="fa-folder-open" title="Tiada data" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-card>
@endsection
