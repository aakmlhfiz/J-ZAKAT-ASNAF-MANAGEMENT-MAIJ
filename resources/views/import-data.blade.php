@extends('layouts.app')

@section('title', 'Import Data - J-ZAKAT AI')

@php
    $title = 'Import Data';
    $subtitle = 'Muat naik data pemohon melalui CSV atau PDF';
@endphp

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('importTabs', () => ({
            tab: 'csv',

            selectTab(t) {
                this.tab = t;
                try { localStorage.setItem('import-tab', t); } catch(e) {}
            },

            init() {
                try { const saved = localStorage.getItem('import-tab'); if (saved) this.tab = saved; } catch(e) {}
            }
        }));

        Alpine.data('pdfUploader', () => ({
            dragging: false,
            fileName: '',
            uploading: false,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) this.fileName = file.name;
            },

            handleDrop(event) {
                this.dragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.fileName = file.name;
                }
            },

            clearFile() {
                this.fileName = '';
                this.$refs.fileInput.value = '';
            }
        }));
    });
</script>
@endpush

@section('content')
<div x-data="importTabs">

<x-page-header title="Import Data" subtitle="Muat naik data pemohon secara pukal melalui CSV atau dokumen PDF" icon="fa-file-import" eyebrow="Pengurusan Data" />

{{-- Tabs --}}
<div class="flex flex-wrap gap-1 mb-6 border-b border-divider pb-0">
    <button @click="selectTab('csv')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'csv' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-file-csv text-[12px] mr-1.5"></i> CSV (Pukal)
    </button>
    <button @click="selectTab('pdf')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'pdf' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-file-pdf text-[12px] mr-1.5"></i> PDF / OCR
    </button>
    <button @click="selectTab('history')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'history' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-clock-rotate-left text-[12px] mr-1.5"></i> Sejarah Import
    </button>
</div>

{{-- TAB: CSV --}}
<div x-show="tab === 'csv'" x-cloak class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Muat Naik Fail CSV" description="Pilih atau tarik fail CSV pemohon ke kawasan di bawah" icon="fa-cloud-arrow-up" />
                <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                    @csrf
                    <x-file-dropzone name="csv" accept=".csv" label="Tarik fail CSV ke sini atau klik untuk muat naik" hint="Saiz maksimum: 10MB" />
                    <div class="flex items-center gap-3 mt-6 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-upload mr-1.5 text-[12px]"></i> Muat Naik & Import
                        </button>
                        <a href="{{ route('importcsv.template') }}" class="btn btn-outline">
                            <i class="fa-solid fa-download mr-1.5 text-[12px]"></i> Muat Turun Templat
                        </a>
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
                            <tr><td class="font-medium text-text">nama</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Nama penuh pemohon</td></tr>
                            <tr><td class="font-medium text-text">ic</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Nombor kad pengenalan (unik)</td></tr>
                            <tr><td class="font-medium text-text">ic_ketua_rumah</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">IC ketua isi rumah</td></tr>
                            <tr><td class="font-medium text-text">hubungan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Hubungan dengan ketua</td></tr>
                            <tr><td class="font-medium text-text">pendapatan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Pendapatan bulanan (RM)</td></tr>
                            <tr><td class="font-medium text-text">bilangan_tanggungan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Bilangan tanggungan</td></tr>
                            <tr><td class="font-medium text-text">sewa</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Sewa rumah (RM)</td></tr>
                            <tr><td class="font-medium text-text">utiliti</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bil utiliti (RM)</td></tr>
                            <tr><td class="font-medium text-text">telefon</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Nombor telefon</td></tr>
                            <tr><td class="font-medium text-text">alamat</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">Wajib</span></td><td class="text-secondary">Alamat penuh</td></tr>
                            <tr><td class="font-medium text-text">n_isteri_suami</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan isteri/suami</td></tr>
                            <tr><td class="font-medium text-text">n_ibu_bapa</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan ibu bapa tanggungan</td></tr>
                            <tr><td class="font-medium text-text">n_dewasa_tidak_bekerja</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan dewasa tidak bekerja</td></tr>
                            <tr><td class="font-medium text-text">n_dewasa_bekerja_tanggung</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan dewasa bekerja ditanggung</td></tr>
                            <tr><td class="font-medium text-text">n_ipt</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan pelajar IPT</td></tr>
                            <tr><td class="font-medium text-text">n_remaja</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan remaja sekolah menengah</td></tr>
                            <tr><td class="font-medium text-text">n_kanak_rendah</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan kanak-kanak sekolah rendah</td></tr>
                            <tr><td class="font-medium text-text">n_kanak_tadika</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Bilangan kanak-kanak tadika</td></tr>
                            <tr><td class="font-medium text-text">perumahan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">sendiri / sewa</td></tr>
                            <tr><td class="font-medium text-text">pengangkutan_input</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Kos pengangkutan (RM)</td></tr>
                            <tr><td class="font-medium text-text">perubatan_input</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">Pilihan</span></td><td class="text-secondary">Kos perubatan (RM)</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-muted/50 p-4 rounded-[10px] mt-4 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                    <p class="text-small text-text leading-relaxed">Lajur Pilihan akan dianggap <code class="font-mono text-[12px]">0</code> (atau <code class="font-mono text-[12px]">sendiri</code> untuk perumahan) jika dikosongkan.</p>
                </div>
            </x-app-card>
        </div>

        <div class="space-y-6">
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header title="Sejarah CSV" description="10 import terkini" icon="fa-clock-rotate-left" />
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-text tabular-nums">{{ $csvStats['total'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Jumlah</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-success tabular-nums">{{ $csvStats['berjaya'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Berjaya</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-danger tabular-nums">{{ $csvStats['gagal'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Gagal</p>
                    </div>
                </div>
                @if($csvHistory->isEmpty())
                    <x-empty-state icon="fa-file-csv" title="Belum ada import CSV" description="Sejarah import CSV akan dipaparkan di sini." />
                @else
                    <ul class="space-y-2">
                        @foreach($csvHistory as $h)
                            <li class="p-3 rounded-[10px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-body text-text font-medium truncate flex-1">{{ $h->nama_fail }}</p>
                                    <span class="text-eyebrow text-tertiary shrink-0">{{ $h->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2 text-small">
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
</div>

{{-- TAB: PDF / OCR --}}
<div x-show="tab === 'pdf'" x-cloak class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Muat Naik Borang" description="Pilih atau tarik fail borang permohonan (PDF, JPG, PNG)" icon="fa-cloud-arrow-up" />

                <div x-data="pdfUploader" class="space-y-5">
                    <form id="upload-form" method="POST" action="{{ route('import-pdf.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="handleDrop($event)"
                             @click="$refs.fileInput.click()"
                             class="relative border-2 border-dashed rounded-[20px] p-12 text-center cursor-pointer transition-apple"
                             :class="dragging ? 'border-primary bg-primary-soft scale-[1.01]' : 'border-divider hover:border-primary/50 hover:bg-primary-soft/30'">

                            <input type="file" name="file" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
                                   @change="handleFileSelect($event)">

                            <div class="w-16 h-16 rounded-[20px] bg-primary-soft mx-auto flex items-center justify-center mb-4 ring-1 ring-primary/15">
                                <i class="fa-solid fa-cloud-arrow-up text-primary text-[26px]"></i>
                            </div>

                            <p class="text-h3 text-text" x-text="fileName || 'Tarik fail ke sini atau klik'"></p>
                            <p class="text-small text-secondary mt-2">Format disokong: PDF, JPG, PNG</p>
                            <p class="text-eyebrow text-tertiary mt-3">Saiz maksimum: 10MB</p>

                            <div x-show="fileName" x-cloak class="mt-5 bg-muted/50 inline-flex items-center gap-2 px-4 py-2 rounded-pill">
                                <i class="fa-solid fa-file text-primary text-[13px]"></i>
                                <span class="text-small text-text font-medium" x-text="fileName"></span>
                                <button @click.stop="clearFile()" type="button" class="w-5 h-5 rounded-full hover:bg-danger/10 flex items-center justify-center text-tertiary hover:text-danger transition-fast">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>

                            <div x-show="uploading" x-cloak class="mt-6 max-w-xs mx-auto">
                                <div class="h-2 bg-muted rounded-pill overflow-hidden">
                                    <div class="h-full bg-primary rounded-pill transition-all duration-300" style="width: 75%"></div>
                                </div>
                                <p class="text-small text-secondary mt-2">Memuat naik...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-6 flex-wrap">
                            <button type="submit" class="btn btn-primary" :disabled="!fileName || uploading">
                                <i class="fa-solid fa-upload mr-1.5 text-[12px]"></i> Muat Naik & Ekstrak
                            </button>
                            <a href="{{ route('import-pdf.history') }}" class="btn btn-outline">
                                <i class="fa-solid fa-clock-rotate-left mr-1.5 text-[12px]"></i> Sejarah PDF
                            </a>
                        </div>
                    </form>

                    <div class="bg-muted/50 p-4 rounded-[10px] flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                        <div class="text-small text-text leading-relaxed">
                            <p class="font-semibold mb-1">Bagaimana ia berfungsi?</p>
                            <ol class="list-decimal ml-4 space-y-0.5 text-secondary">
                                <li>Muat naik borang (PDF, JPG, atau PNG)</li>
                                <li>AI mengekstrak teks dari dokumen</li>
                                <li>Sistem mengesan medan secara pintar</li>
                                <li>Semak dan edit data dalam pratinjau</li>
                                <li>Simpan ke pangkalan data profil asnaf</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Medan yang Dikesan" description="Sistem mengecam medan berikut secara automatik" icon="fa-magnifying-glass" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                        $detectedFields = [
                            ['label' => 'Nama', 'aliases' => 'Nama Pemohon, Full Name', 'icon' => 'fa-user'],
                            ['label' => 'No IC', 'aliases' => 'NRIC, IC Number, MyKad', 'icon' => 'fa-id-card'],
                            ['label' => 'No Telefon', 'aliases' => 'Phone, Mobile, No. HP', 'icon' => 'fa-phone'],
                            ['label' => 'Alamat', 'aliases' => 'Address, Home Address', 'icon' => 'fa-location-dot'],
                            ['label' => 'Pendapatan', 'aliases' => 'Income, Monthly Salary', 'icon' => 'fa-money-bill'],
                            ['label' => 'Bil. Tanggungan', 'aliases' => 'Dependents', 'icon' => 'fa-people-group'],
                            ['label' => 'Sewa', 'aliases' => 'Rent, House Rent', 'icon' => 'fa-house'],
                            ['label' => 'Utiliti', 'aliases' => 'Utilities', 'icon' => 'fa-bolt'],
                            ['label' => 'Hubungan', 'aliases' => 'Relationship', 'icon' => 'fa-user-group'],
                            ['label' => 'IC Ketua Rumah', 'aliases' => 'Head of Family IC', 'icon' => 'fa-id-card'],
                        ];
                    @endphp
                    @foreach($detectedFields as $field)
                        <div class="flex items-center gap-3 p-3 rounded-[10px] hover:bg-muted transition-apple">
                            <div class="w-8 h-8 rounded-[8px] bg-primary-soft flex items-center justify-center shrink-0">
                                <i class="fa-solid {{ $field['icon'] }} text-primary text-[12px]"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-small font-semibold text-text">{{ $field['label'] }}</p>
                                <p class="text-eyebrow text-tertiary truncate">{{ $field['aliases'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-app-card>
        </div>

        <div class="space-y-6">
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header title="Sejarah PDF" description="5 import terkini" icon="fa-clock-rotate-left" />
                @if($pdfHistory->isEmpty())
                    <x-empty-state icon="fa-file-pdf" title="Belum ada import PDF" description="Sejarah import PDF akan dipaparkan di sini." />
                @else
                    <ul class="space-y-2">
                        @foreach($pdfHistory->take(5) as $h)
                            <li class="p-3 rounded-[10px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-body text-text font-medium truncate flex-1" title="{{ $h->original_filename }}">{{ $h->original_filename }}</p>
                                    <x-status-badge :status="match($h->status) { 'extracted','committed' => 'success', 'failed' => 'danger', default => 'warning' }" size="sm">{{ $h->status }}</x-status-badge>
                                </div>
                                <p class="text-eyebrow text-tertiary mt-1.5">{{ $h->created_at->format('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-3 border-t border-divider">
                        <a href="{{ route('import-pdf.history') }}" class="text-[12px] font-semibold text-primary hover:underline">Lihat Semua <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i></a>
                    </div>
                @endif
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header title="Statistik OCR" icon="fa-chart-simple" />
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-text tabular-nums">{{ $pdfStats['total'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Jumlah</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-success tabular-nums">{{ $pdfStats['committed'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Disimpan</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-warning tabular-nums">{{ $pdfStats['extracted'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Diekstrak</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-danger tabular-nums">{{ $pdfStats['failed'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">Gagal</p>
                    </div>
                </div>
            </x-app-card>
        </div>
    </div>
</div>

{{-- TAB: Sejarah Import (Combined) --}}
<div x-show="tab === 'history'" x-cloak class="animate-fade-in">
    <x-section-header title="Sejarah Import" description="Gabungan import CSV dan PDF — 15 terkini" icon="fa-clock-rotate-left" />

    @if($combinedHistory->isEmpty())
        <x-empty-state icon="fa-clock-rotate-left" title="Tiada sejarah import" description="Sejarah import CSV dan PDF akan dipaparkan di sini." />
    @else
        <div class="space-y-0">
            @foreach($combinedHistory as $item)
                <div class="flex items-center gap-4 py-3.5 px-4 rounded-[8px] hover:bg-muted/50 transition-fast border-b border-divider last:border-0">
                    <div class="w-9 h-9 rounded-full {{ $item->type === 'csv' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }} flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $item->type === 'csv' ? 'fa-file-csv' : 'fa-file-pdf' }} text-[14px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-body font-semibold text-text truncate">{{ $item->title }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-bold rounded-pill {{ $item->type === 'csv' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                {{ strtoupper($item->type) }}
                            </span>
                        </div>
                        <p class="text-small text-secondary mt-0.5">{{ $item->detail }}</p>
                    </div>
                    <div class="shrink-0 flex items-center gap-3">
                        <span class="text-eyebrow text-tertiary">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</span>
                        <x-status-badge :status="$item->status" size="sm">
                            {{ $item->status === 'success' ? 'Berjaya' : ($item->status === 'danger' ? 'Gagal' : 'Separa') }}
                        </x-status-badge>
                        @if($item->url)
                            <a href="{{ $item->url }}" class="text-[11px] text-primary font-semibold hover:underline">Lihat</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</div>
@endsection
