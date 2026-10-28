@extends('layouts.app')

@section('title', __('imports.data.meta_title'))

@php
    $title = __('imports.data.title');
    $subtitle = __('imports.data.subtitle');
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

<x-page-header :title="__('imports.data.title')" :subtitle="__('imports.data.header_subtitle')" :eyebrow="__('imports.data.eyebrow')" icon="fa-file-import" />

{{-- Tabs --}}
<div class="flex flex-wrap gap-1 mb-6 border-b border-divider pb-0">
    <button @click="selectTab('csv')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'csv' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-file-csv text-[12px] mr-1.5"></i> {{ __('imports.tabs.csv') }}
    </button>
    <button @click="selectTab('pdf')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'pdf' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-file-pdf text-[12px] mr-1.5"></i> {{ __('imports.tabs.pdf') }}
    </button>
    <button @click="selectTab('history')"
        class="relative px-5 py-2.5 text-[13px] font-semibold transition-apple rounded-t-[10px] -mb-px"
        :class="tab === 'history' ? 'text-primary bg-card border border-divider border-b-card' : 'text-secondary hover:text-text hover:bg-muted'">
        <i class="fa-solid fa-clock-rotate-left text-[12px] mr-1.5"></i> {{ __('imports.tabs.history') }}
    </button>
</div>

{{-- TAB: CSV --}}
<div x-show="tab === 'csv'" x-cloak class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.csv.upload_title')" :description="__('imports.csv.upload_description')" icon="fa-cloud-arrow-up" />
                <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                    @csrf
                    <x-file-dropzone name="csv" accept=".csv" :label="__('imports.csv.dropzone_label')" :hint="__('imports.csv.dropzone_hint')" />
                    <div class="flex items-center gap-3 mt-6 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-upload mr-1.5 text-[12px]"></i> {{ __('imports.csv.upload_import') }}
                        </button>
                        <a href="{{ route('importcsv.template') }}" class="btn btn-outline">
                            <i class="fa-solid fa-download mr-1.5 text-[12px]"></i> {{ __('imports.csv.download_template') }}
                        </a>
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
                            <tr><td class="font-medium text-text">nama</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.nama') }}</td></tr>
                            <tr><td class="font-medium text-text">ic</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.ic') }}</td></tr>
                            <tr><td class="font-medium text-text">ic_ketua_rumah</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.ic_ketua_rumah') }}</td></tr>
                            <tr><td class="font-medium text-text">hubungan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.hubungan') }}</td></tr>
                            <tr><td class="font-medium text-text">pendapatan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.pendapatan') }}</td></tr>
                            <tr><td class="font-medium text-text">bilangan_tanggungan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.bilangan_tanggungan') }}</td></tr>
                            <tr><td class="font-medium text-text">sewa</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.sewa') }}</td></tr>
                            <tr><td class="font-medium text-text">utiliti</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.utiliti') }}</td></tr>
                            <tr><td class="font-medium text-text">telefon</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.telefon') }}</td></tr>
                            <tr><td class="font-medium text-text">alamat</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">{{ __('imports.csv.required') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.alamat') }}</td></tr>
                            <tr><td class="font-medium text-text">n_isteri_suami</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_isteri_suami') }}</td></tr>
                            <tr><td class="font-medium text-text">n_ibu_bapa</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_ibu_bapa') }}</td></tr>
                            <tr><td class="font-medium text-text">n_dewasa_tidak_bekerja</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_dewasa_tidak_bekerja') }}</td></tr>
                            <tr><td class="font-medium text-text">n_dewasa_bekerja_tanggung</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_dewasa_bekerja_tanggung') }}</td></tr>
                            <tr><td class="font-medium text-text">n_ipt</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_ipt') }}</td></tr>
                            <tr><td class="font-medium text-text">n_remaja</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_remaja') }}</td></tr>
                            <tr><td class="font-medium text-text">n_kanak_rendah</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_kanak_rendah') }}</td></tr>
                            <tr><td class="font-medium text-text">n_kanak_tadika</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.n_kanak_tadika') }}</td></tr>
                            <tr><td class="font-medium text-text">perumahan</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.perumahan') }}</td></tr>
                            <tr><td class="font-medium text-text">pengangkutan_input</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.pengangkutan_input') }}</td></tr>
                            <tr><td class="font-medium text-text">perubatan_input</td><td><span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">{{ __('imports.csv.optional') }}</span></td><td class="text-secondary">{{ __('imports.csv_columns.perubatan_input') }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-muted/50 p-4 rounded-[10px] mt-4 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                    <p class="text-small text-text leading-relaxed">{!! __('imports.csv.optional_note_short') !!}</p>
                </div>
            </x-app-card>
        </div>

        <div class="space-y-6">
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('imports.csv.history_csv_title')" :description="__('imports.csv.history_description')" icon="fa-clock-rotate-left" />
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-text tabular-nums">{{ $csvStats['total'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.total') }}</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-success tabular-nums">{{ $csvStats['berjaya'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('common.status.success') }}</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-danger tabular-nums">{{ $csvStats['gagal'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.failed') }}</p>
                    </div>
                </div>
                @if($csvHistory->isEmpty())
                    <x-empty-state icon="fa-file-csv" :title="__('imports.csv.empty_csv_title')" :description="__('imports.csv.empty_description')" />
                @else
                    <ul class="space-y-2">
                        @foreach($csvHistory as $h)
                            <li class="p-3 rounded-[10px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-body text-text font-medium truncate flex-1">{{ $h->nama_fail }}</p>
                                    <span class="text-eyebrow text-tertiary shrink-0">{{ $h->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2 text-small">
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
</div>

{{-- TAB: PDF / OCR --}}
<div x-show="tab === 'pdf'" x-cloak class="animate-fade-in">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.pdf.upload_form_title')" :description="__('imports.pdf.upload_form_description')" icon="fa-cloud-arrow-up" />

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

                            <p class="text-h3 text-text" x-text="fileName || @js(__('imports.pdf.drop_title'))"></p>
                            <p class="text-small text-secondary mt-2">{{ __('imports.pdf.formats') }}</p>
                            <p class="text-eyebrow text-tertiary mt-3">{{ __('imports.pdf.max_size') }}</p>

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
                                <p class="text-small text-secondary mt-2">{{ __('imports.pdf.ai_processing') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-6 flex-wrap">
                            <button type="submit" class="btn btn-primary" :disabled="!fileName || uploading">
                                <i class="fa-solid fa-upload mr-1.5 text-[12px]"></i> {{ __('imports.pdf.upload_extract') }}
                            </button>
                            <a href="{{ route('import-pdf.history') }}" class="btn btn-outline">
                                <i class="fa-solid fa-clock-rotate-left mr-1.5 text-[12px]"></i> {{ __('imports.pdf.recent_pdf_title') }}
                            </a>
                        </div>
                    </form>

                    <div class="bg-muted/50 p-4 rounded-[10px] flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                        <div class="text-small text-text leading-relaxed">
                            <p class="font-semibold mb-1">{{ __('imports.pdf.how_title') }}</p>
                            <ol class="list-decimal ml-4 space-y-0.5 text-secondary">
                                @foreach(__('imports.pdf.how_steps') as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.pdf.detected_title')" :description="__('imports.pdf.detected_description')" icon="fa-magnifying-glass" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                        $detectedFields = [
                            ['label' => __('imports.detected.name'), 'aliases' => 'Nama Pemohon, Full Name', 'icon' => 'fa-user'],
                            ['label' => __('imports.detected.ic'), 'aliases' => 'NRIC, IC Number, MyKad', 'icon' => 'fa-id-card'],
                            ['label' => __('imports.detected.phone'), 'aliases' => 'Phone, Mobile, No. HP', 'icon' => 'fa-phone'],
                            ['label' => __('imports.detected.address'), 'aliases' => 'Address, Home Address', 'icon' => 'fa-location-dot'],
                            ['label' => __('imports.detected.income'), 'aliases' => 'Income, Monthly Salary', 'icon' => 'fa-money-bill'],
                            ['label' => __('imports.detected.dependents'), 'aliases' => 'Dependents', 'icon' => 'fa-people-group'],
                            ['label' => __('imports.detected.rent'), 'aliases' => 'Rent, House Rent', 'icon' => 'fa-house'],
                            ['label' => __('imports.detected.utilities'), 'aliases' => 'Utilities', 'icon' => 'fa-bolt'],
                            ['label' => __('imports.detected.relationship'), 'aliases' => 'Relationship', 'icon' => 'fa-user-group'],
                            ['label' => __('imports.detected.head_ic'), 'aliases' => 'Head of Family IC', 'icon' => 'fa-id-card'],
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
                <x-section-header :title="__('imports.pdf.recent_pdf_title')" :description="__('imports.pdf.recent_pdf_description')" icon="fa-clock-rotate-left" />
                @if($pdfHistory->isEmpty())
                    <x-empty-state icon="fa-file-pdf" :title="__('imports.pdf.empty_pdf_title')" :description="__('imports.pdf.empty_pdf_description')" />
                @else
                    <ul class="space-y-2">
                        @foreach($pdfHistory->take(5) as $h)
                            <li class="p-3 rounded-[10px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-body text-text font-medium truncate flex-1" title="{{ $h->original_filename }}">{{ $h->original_filename }}</p>
                                    <x-status-badge :status="match($h->status) { 'extracted','committed' => 'success', 'failed' => 'danger', default => 'warning' }" size="sm">{{ match($h->status) {
                                        'extracted' => __('imports.status.extracted'),
                                        'needs_review' => __('imports.status.needs_review'),
                                        'committed' => __('imports.status.committed'),
                                        'failed' => __('imports.status.failed'),
                                        'processing' => __('imports.status.processing'),
                                        default => $h->status,
                                    } }}</x-status-badge>
                                </div>
                                <p class="text-eyebrow text-tertiary mt-1.5">{{ $h->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-3 border-t border-divider">
                        <a href="{{ route('import-pdf.history') }}" class="text-[12px] font-semibold text-primary hover:underline">{{ __('imports.csv.view_all') }} <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i></a>
                    </div>
                @endif
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('imports.pdf.ocr_stats_title')" icon="fa-chart-simple" />
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-text tabular-nums">{{ $pdfStats['total'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.total') }}</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-success tabular-nums">{{ $pdfStats['committed'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.committed') }}</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-warning tabular-nums">{{ $pdfStats['extracted'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.extracted') }}</p>
                    </div>
                    <div class="text-center p-3 rounded-[10px] bg-muted/50">
                        <p class="text-metric-sm text-danger tabular-nums">{{ $pdfStats['failed'] }}</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.failed') }}</p>
                    </div>
                </div>
            </x-app-card>
        </div>
    </div>
</div>

{{-- TAB: Sejarah Import (Combined) --}}
<div x-show="tab === 'history'" x-cloak class="animate-fade-in">
    <x-section-header :title="__('imports.data.combined_title')" :description="__('imports.data.combined_description')" icon="fa-clock-rotate-left" />

    @if($combinedHistory->isEmpty())
        <x-empty-state icon="fa-clock-rotate-left" :title="__('imports.data.combined_empty_title')" :description="__('imports.data.combined_empty_description')" />
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
                        <span class="text-eyebrow text-tertiary">{{ \Carbon\Carbon::parse($item->date)->locale(app()->getLocale())->translatedFormat('d M Y') }}</span>
                        <x-status-badge :status="$item->status" size="sm">
                            {{ $item->status === 'success' ? __('imports.data.success') : ($item->status === 'danger' ? __('imports.status.failed') : __('imports.status.partial')) }}
                        </x-status-badge>
                        @if($item->url)
                            <a href="{{ $item->url }}" class="text-[11px] text-primary font-semibold hover:underline">{{ __('imports.data.view') }}</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</div>
@endsection
