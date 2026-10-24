@extends('layouts.app', [
    'title' => 'Import CSV',
    'subtitle' => 'Muat naik data pemohon secara pukal',
])
@section('content')

<x-page-header title="Import CSV" subtitle="Muat naik data pemohon secara pukal" eyebrow="Pengurusan Data" icon="fa-file-import">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('reports.pdf') }}">Eksport PDF</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-file-excel" href="{{ route('reports.excel') }}">Eksport Excel</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-stepper :steps="['Muat Naik', 'Sah & Pratinjau', 'Import', 'Selesai']" :current="0" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Muat Naik Fail CSV" description="Pilih atau tarik fail CSV pemohon ke kawasan di bawah" icon="fa-cloud-arrow-up" />
            <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                @csrf
                <x-file-dropzone name="csv" accept=".csv" label="Tarik fail CSV ke sini atau klik untuk muat naik" hint="Saiz maksimum: 10MB" />
                <div class="flex items-center gap-3 mt-6 flex-wrap">
                    <x-app-button type="submit" variant="primary" size="lg" icon="fa-upload">Muat Naik &amp; Import</x-app-button>
                    <x-app-button variant="outline" size="lg" icon="fa-download" href="{{ route('importcsv.template') }}">Muat Turun Templat</x-app-button>
                </div>
            </form>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Format CSV" description="Pastikan fail CSV mengandungi lajur berikut" icon="fa-table-list" />
            <div class="overflow-x-auto">
                <table class="app-table is-compact">
                    <thead>
                        <tr>
                            <th>Lajur</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="font-medium text-text">nama</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Nama penuh pemohon</td></tr>
                        <tr><td class="font-medium text-text">ic</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Nombor kad pengenalan (unik)</td></tr>
                        <tr><td class="font-medium text-text">ic_ketua_rumah</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">IC ketua isi rumah</td></tr>
                        <tr><td class="font-medium text-text">hubungan</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Hubungan dengan ketua</td></tr>
                        <tr><td class="font-medium text-text">pendapatan</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Pendapatan bulanan (RM)</td></tr>
                        <tr><td class="font-medium text-text">bilangan_tanggungan</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Bilangan tanggungan</td></tr>
                        <tr><td class="font-medium text-text">sewa</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Sewa rumah (RM)</td></tr>
                        <tr><td class="font-medium text-text">utiliti</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bil utiliti (RM)</td></tr>
                        <tr><td class="font-medium text-text">telefon</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Nombor telefon</td></tr>
                        <tr><td class="font-medium text-text">alamat</td><td><x-status-badge status="danger" :dot="false">Wajib</x-status-badge></td><td class="text-secondary">Alamat penuh</td></tr>
                        <tr><td class="font-medium text-text">n_isteri_suami</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan isteri/suami</td></tr>
                        <tr><td class="font-medium text-text">n_ibu_bapa</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan ibu bapa tanggungan</td></tr>
                        <tr><td class="font-medium text-text">n_dewasa_tidak_bekerja</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan dewasa tidak bekerja</td></tr>
                        <tr><td class="font-medium text-text">n_dewasa_bekerja_tanggung</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan dewasa bekerja yang ditanggung</td></tr>
                        <tr><td class="font-medium text-text">n_ipt</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan dewasa belajar IPT</td></tr>
                        <tr><td class="font-medium text-text">n_remaja</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan remaja sekolah menengah</td></tr>
                        <tr><td class="font-medium text-text">n_kanak_rendah</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan kanak-kanak sekolah rendah</td></tr>
                        <tr><td class="font-medium text-text">n_kanak_tadika</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Bilangan kanak-kanak tadika</td></tr>
                        <tr><td class="font-medium text-text">perumahan</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">sendiri / sewa</td></tr>
                        <tr><td class="font-medium text-text">pengangkutan_input</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Kos pengangkutan (RM)</td></tr>
                        <tr><td class="font-medium text-text">perubatan_input</td><td><x-status-badge status="warning" :dot="false">Pilihan</x-status-badge></td><td class="text-secondary">Kos perubatan (RM)</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="soft-panel p-4 mt-4 flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                <p class="text-small text-text leading-relaxed">Semua lajur Pilihan di atas adalah pilihan. Jika dikosongkan, enjin akan anggap <code class="font-mono text-[12px]">0</code> (atau <code class="font-mono text-[12px]">sendiri</code> untuk perumahan).</p>
            </div>
        </x-app-card>
    </div>

    <div class="space-y-6">
        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header title="Sejarah Import" description="10 import terkini" icon="fa-clock-rotate-left" />
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Jumlah</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $stats['berjaya'] ?? 0 }}</p>
                    <p class="text-eyebrow text-success mt-1">Berjaya</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $stats['gagal'] ?? 0 }}</p>
                    <p class="text-eyebrow text-danger mt-1">Gagal</p>
                </div>
            </div>

            @if($history->isEmpty())
                <x-empty-state icon="fa-clock-rotate-left" title="Belum ada import" description="Sejarah import CSV akan dipaparkan di sini." />
            @else
                <ul class="space-y-2">
                    @foreach($history as $h)
                        <li class="p-3 rounded-[12px] hover:bg-muted transition-apple">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-body text-text font-medium truncate flex-1">{{ $h->nama_fail }}</p>
                                <span class="text-eyebrow text-tertiary shrink-0">{{ $h->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-small">
                                <span class="text-secondary tabular-nums">{{ $h->jumlah_rekod }} rekod</span>
                                <span class="text-tertiary">·</span>
                                <span class="text-success font-semibold">{{ $h->berjaya }} ok</span>
                                @if($h->gagal > 0)
                                    <span class="text-tertiary">·</span>
                                    <span class="text-danger font-semibold">{{ $h->gagal }} gagal</span>
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
