@extends('layouts.app', [
    'title' => __('imports.csv.title'),
    'subtitle' => __('imports.csv.subtitle'),
])
@section('content')

<x-page-header :title="__('imports.csv.title')" :subtitle="__('imports.csv.subtitle')" :eyebrow="__('imports.csv.eyebrow')" icon="fa-file-import">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('reports.pdf') }}">{{ __('imports.csv.export_pdf') }}</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-file-excel" href="{{ route('reports.excel') }}">{{ __('imports.csv.export_excel') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-stepper :steps="__('imports.steps.csv')" :current="0" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('imports.csv.upload_title')" :description="__('imports.csv.upload_description')" icon="fa-cloud-arrow-up" />
            <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                @csrf
                <x-file-dropzone name="csv" accept=".csv" :label="__('imports.csv.dropzone_label')" :hint="__('imports.csv.dropzone_hint')" />
                <div class="flex items-center gap-3 mt-6 flex-wrap">
                    <x-app-button type="submit" variant="primary" size="lg" icon="fa-upload">{{ __('imports.csv.upload_import') }}</x-app-button>
                    <x-app-button variant="outline" size="lg" icon="fa-download" href="{{ route('importcsv.template') }}">{{ __('imports.csv.download_template') }}</x-app-button>
                </div>
            </form>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('imports.csv.format_title')" :description="__('imports.csv.format_description')" icon="fa-table-list" />
            <div class="overflow-x-auto">
                <table class="app-table is-compact">
                    <thead>
                        <tr>
                            <th>{{ __('imports.csv.col_column') }}</th>
                            <th>{{ __('imports.csv.col_type') }}</th>
                            <th>{{ __('imports.csv.col_description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="font-medium text-text">nama</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.nama') }}</td></tr>
                        <tr><td class="font-medium text-text">ic</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.ic') }}</td></tr>
                        <tr><td class="font-medium text-text">ic_ketua_rumah</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.ic_ketua_rumah') }}</td></tr>
                        <tr><td class="font-medium text-text">hubungan</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.hubungan') }}</td></tr>
                        <tr><td class="font-medium text-text">pendapatan</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.pendapatan') }}</td></tr>
                        <tr><td class="font-medium text-text">bilangan_tanggungan</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.bilangan_tanggungan') }}</td></tr>
                        <tr><td class="font-medium text-text">sewa</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.sewa') }}</td></tr>
                        <tr><td class="font-medium text-text">utiliti</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.utiliti') }}</td></tr>
                        <tr><td class="font-medium text-text">telefon</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.telefon') }}</td></tr>
                        <tr><td class="font-medium text-text">alamat</td><td><x-status-badge status="danger" :dot="false">{{ __('imports.csv.required') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.alamat') }}</td></tr>
                        <tr><td class="font-medium text-text">n_isteri_suami</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_isteri_suami') }}</td></tr>
                        <tr><td class="font-medium text-text">n_ibu_bapa</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_ibu_bapa') }}</td></tr>
                        <tr><td class="font-medium text-text">n_dewasa_tidak_bekerja</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_dewasa_tidak_bekerja') }}</td></tr>
                        <tr><td class="font-medium text-text">n_dewasa_bekerja_tanggung</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_dewasa_bekerja_tanggung') }}</td></tr>
                        <tr><td class="font-medium text-text">n_ipt</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_ipt') }}</td></tr>
                        <tr><td class="font-medium text-text">n_remaja</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_remaja') }}</td></tr>
                        <tr><td class="font-medium text-text">n_kanak_rendah</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_kanak_rendah') }}</td></tr>
                        <tr><td class="font-medium text-text">n_kanak_tadika</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.n_kanak_tadika') }}</td></tr>
                        <tr><td class="font-medium text-text">perumahan</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.perumahan') }}</td></tr>
                        <tr><td class="font-medium text-text">pengangkutan_input</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.pengangkutan_input') }}</td></tr>
                        <tr><td class="font-medium text-text">perubatan_input</td><td><x-status-badge status="warning" :dot="false">{{ __('imports.csv.optional') }}</x-status-badge></td><td class="text-secondary">{{ __('imports.csv_columns.perubatan_input') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="soft-panel p-4 mt-4 flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                <p class="text-small text-text leading-relaxed">{!! __('imports.csv.optional_note') !!}</p>
            </div>
        </x-app-card>
    </div>

    <div class="space-y-6">
        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header :title="__('imports.csv.history_title')" :description="__('imports.csv.history_description')" icon="fa-clock-rotate-left" />
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.total') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $stats['berjaya'] ?? 0 }}</p>
                    <p class="text-eyebrow text-success mt-1">{{ __('common.status.success') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $stats['gagal'] ?? 0 }}</p>
                    <p class="text-eyebrow text-danger mt-1">{{ __('imports.status.failed') }}</p>
                </div>
            </div>

            @if($history->isEmpty())
                <x-empty-state icon="fa-clock-rotate-left" :title="__('imports.csv.empty_title')" :description="__('imports.csv.empty_description')" />
            @else
                <ul class="space-y-2">
                    @foreach($history as $h)
                        <li class="p-3 rounded-[12px] hover:bg-muted transition-apple">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-body text-text font-medium truncate flex-1">{{ $h->nama_fail }}</p>
                                <span class="text-eyebrow text-tertiary shrink-0">{{ $h->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-small">
                                <span class="text-secondary tabular-nums">{{ __('imports.records', ['count' => $h->jumlah_rekod]) }}</span>
                                <span class="text-tertiary">·</span>
                                <span class="text-success font-semibold">{{ __('imports.ok', ['count' => $h->berjaya]) }}</span>
                                @if($h->gagal > 0)
                                    <span class="text-tertiary">·</span>
                                    <span class="text-danger font-semibold">{{ __('imports.failed_count', ['count' => $h->gagal]) }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-app-card>
    </div>
</div>
@endsection
