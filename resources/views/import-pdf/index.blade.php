@extends('layouts.app', [
    'title' => __('imports.pdf.meta_title'),
    'subtitle' => __('imports.pdf.meta_subtitle'),
])
@section('content')

<x-page-header :title="__('imports.pdf.title')" :subtitle="__('imports.pdf.subtitle')" :eyebrow="__('imports.pdf.eyebrow')" icon="fa-file-pdf">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.template') }}">{{ __('imports.pdf.download_blank_form') }}</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-clock-rotate-left" href="{{ route('import-pdf.history') }}">{{ __('imports.pdf.history_button') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-app-card class="p-6 mb-6" :hover="false" elevated>
    <x-stepper :steps="__('imports.steps.pdf')" :current="1" />
</x-app-card>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('imports.pdf.csv_import_title')" :description="__('imports.pdf.csv_import_description')" icon="fa-file-csv" />

            <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                @csrf
                <x-file-dropzone name="csv" accept=".csv" :label="__('imports.csv.dropzone_label')" :hint="__('imports.csv.dropzone_hint')" />

                @error('csv')
                    <p class="text-eyebrow text-danger mt-3">{{ $message }}</p>
                @enderror

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
            <x-section-header :title="__('imports.pdf.upload_form_title')" :description="__('imports.pdf.upload_form_description')" icon="fa-cloud-arrow-up" />

            <div x-data="ocrScanner()" class="space-y-5" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)">
                <form id="upload-form" enctype="multipart/form-data" @submit.prevent="submitUpload()">

                    <div x-show="healthWarning" x-cloak class="mb-5 p-4 rounded-[8px] bg-warning-soft/40 border border-warning/20 text-small text-text">
                        <i class="fa-solid fa-triangle-exclamation text-warning mr-2"></i>
                        <span x-text="healthWarning"></span>
                    </div>

                    <div class="mb-5">
                        <label for="document_type" class="block text-small font-semibold text-text mb-2">
                            <i class="fa-solid fa-file-circle-check text-primary text-[13px] mr-1.5"></i>
                            {{ __('imports.pdf.document_type') }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="document_type" value="borang_bantuan_maij" class="peer sr-only" checked x-model="documentType">
                                <div class="border-2 border-divider rounded-[8px] p-4 peer-checked:border-primary peer-checked:bg-primary-soft/40 transition-apple hover:border-primary/50">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-[10px] bg-primary-soft flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-file-contract text-primary text-[14px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-small font-semibold text-text">{{ __('imports.pdf.doc_type_maij') }}</p>
                                            <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.pdf.doc_type_maij_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="document_type" value="generic" class="peer sr-only" x-model="documentType">
                                <div class="border-2 border-divider rounded-[8px] p-4 peer-checked:border-primary peer-checked:bg-primary-soft/40 transition-apple hover:border-primary/50">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-[10px] bg-muted flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-file-lines text-secondary text-[14px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-small font-semibold text-text">{{ __('imports.pdf.doc_type_generic') }}</p>
                                            <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.pdf.doc_type_generic_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('document_type')
                            <p class="text-eyebrow text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div                          @click="$refs.fileInput.click()"
                         class="relative border-2 border-dashed rounded-[12px] p-12 text-center cursor-pointer transition-apple"
                         :class="dragging ? 'border-primary bg-primary-soft scale-[1.01]' : 'border-divider hover:border-primary/50 hover:bg-primary-soft/30'">

                        <input type="file" name="file" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
                               @change="handleFileSelect($event)">

                        <div class="w-16 h-16 rounded-[12px] bg-primary-soft mx-auto flex items-center justify-center mb-4 ring-1 ring-primary/15">
                            <i class="fa-solid fa-cloud-arrow-up text-primary text-[26px]"></i>
                        </div>

                        <p class="text-h3 text-text" x-text="fileName || @js(__('imports.pdf.drop_title'))"></p>
                        <p class="text-small text-secondary mt-2">{{ __('imports.pdf.formats') }}</p>
                        <p class="text-eyebrow text-tertiary mt-3">{{ __('imports.pdf.max_size') }}</p>

                        <div x-show="fileName" x-cloak class="mt-5 soft-panel inline-flex items-center gap-2 px-4 py-2 rounded-pill">
                            <i class="fa-solid fa-file text-primary text-[13px]"></i>
                            <span class="text-small text-text font-medium" x-text="fileName"></span>
                            <span x-show="fileSize" class="text-eyebrow text-tertiary" x-text="formatSize(fileSize)"></span>
                            <button @click.stop="clearFile()" type="button" class="w-5 h-5 rounded-full hover:bg-danger/10 flex items-center justify-center text-tertiary hover:text-danger transition-fast">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </div>

                        <div x-show="uploading" x-cloak class="mt-6 max-w-md mx-auto w-full">
                            <div class="flex items-center justify-between text-eyebrow text-tertiary mb-1.5">
                                <span x-text="stageLabel"></span>
                                <span class="tabular-nums" x-text="progress + '%'"></span>
                            </div>
                            <div class="h-2.5 bg-muted rounded-pill overflow-hidden">
                                <div class="h-full bg-primary rounded-pill transition-all duration-300" :style="`width: ${progress}%`"></div>
                            </div>
                            <div class="flex justify-between mt-2">
                                <template x-for="s in stageDefs" :key="s.pct">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-[10px] font-semibold" :class="progress >= s.pct ? 'text-primary' : 'text-tertiary'">
                                            <i class="fa-solid" :class="progress >= s.pct ? 'fa-circle-check' : 'fa-circle'"></i>
                                        </span>
                                        <span class="text-[9px] text-tertiary" x-text="s.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-6 flex-wrap">
                        <button type="submit" class="btn btn-primary btn-lg" :disabled="!fileName || uploading">
                            <template x-if="!uploading">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-upload text-[14px]"></i>
                                    <span>{{ __('imports.pdf.upload_extract') }}</span>
                                </span>
                            </template>
                            <template x-if="uploading">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-spinner fa-spin text-[14px]"></i>
                                    <span>{{ __('imports.pdf.ai_processing') }}</span>
                                </span>
                            </template>
                        </button>
                        <x-app-button variant="outline" size="lg" icon="fa-clock-rotate-left" x-bind:disabled="uploading" href="{{ route('import-pdf.history') }}">{{ __('imports.pdf.view_history') }}</x-app-button>
                    </div>

                    <div x-show="uploading" x-cloak class="mt-5 p-4 rounded-[8px] bg-primary-soft/40 border border-primary/15 text-small text-text">
                        <i class="fa-solid fa-circle-info text-primary mr-2"></i>
                        <span x-text="processingHint"></span>
                    </div>

                    <div x-show="stalled" x-cloak class="mt-5 p-4 rounded-[8px] bg-warning-soft/40 border border-warning/20 text-small text-text">
                        <i class="fa-solid fa-triangle-exclamation text-warning mr-2"></i>
                        {{ __('imports.pdf.stalled_message') }}
                        <button type="button" class="ml-2 btn btn-warning btn-xs" @click="startPolling()">{{ __('imports.pdf.retry') }}</button>
                    </div>

                    <div x-show="failed" x-cloak class="mt-5 p-4 rounded-[8px] bg-danger-soft/40 border border-danger/20 text-small text-text">
                        <i class="fa-solid fa-circle-xmark text-danger mr-2"></i>
                        <span x-text="errorMessage || @js(__('imports.pdf.processing_failed_retry'))"></span>
                    </div>
                </form>

                <div class="soft-panel p-4 flex items-start gap-3">
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
                        ['label' => __('imports.maij_detected.name'), 'aliases' => 'Nama Penuh, Full Name, Applicant Name', 'icon' => 'fa-user'],
                        ['label' => __('imports.maij_detected.ic'), 'aliases' => 'No Kad Pengenalan, NRIC, MyKad', 'icon' => 'fa-id-card'],
                        ['label' => __('imports.maij_detected.marital'), 'aliases' => 'Marital Status, Berkahwin/Bujang', 'icon' => 'fa-ring'],
                        ['label' => __('imports.maij_detected.smoking'), 'aliases' => 'Smoking, Perokok', 'icon' => 'fa-smoking'],
                        ['label' => __('imports.maij_detected.mobile'), 'aliases' => 'Mobile, No. HP, Phone', 'icon' => 'fa-mobile-screen'],
                        ['label' => __('imports.maij_detected.home_phone'), 'aliases' => 'Home Phone, Telefon Rumah', 'icon' => 'fa-phone'],
                        ['label' => __('imports.maij_detected.address'), 'aliases' => 'Address, Alamat Surat Menyurat', 'icon' => 'fa-location-dot'],
                        ['label' => __('imports.maij_detected.district'), 'aliases' => 'District', 'icon' => 'fa-map'],
                        ['label' => __('imports.maij_detected.postcode'), 'aliases' => 'Postcode, Postal Code', 'icon' => 'fa-hashtag'],
                        ['label' => __('imports.maij_detected.dun'), 'aliases' => 'State Constituency, Parliament', 'icon' => 'fa-landmark'],
                        ['label' => __('imports.maij_detected.health'), 'aliases' => 'Health Condition, Penyakit', 'icon' => 'fa-heart-pulse'],
                        ['label' => __('imports.maij_detected.income'), 'aliases' => 'Income, Monthly Salary, Gaji', 'icon' => 'fa-money-bill'],
                        ['label' => __('imports.maij_detected.rent_utilities'), 'aliases' => 'Rent, Utilities', 'icon' => 'fa-house'],
                        ['label' => __('imports.maij_detected.transport_medical'), 'aliases' => 'Transport, Medical Expenses', 'icon' => 'fa-car-side'],
                        ['label' => __('imports.maij_detected.assistance_type'), 'aliases' => 'Sara Hidup, Perubatan, Berdikari...', 'icon' => 'fa-hand-holding-heart'],
                        ['label' => __('imports.maij_detected.household_members'), 'aliases' => 'Household Members (Jadual)', 'icon' => 'fa-people-group'],
                    ];
                @endphp
                @foreach($detectedFields as $field)
                    <div class="flex items-center gap-3 p-3 rounded-[12px] hover:bg-muted transition-apple">
                        <div class="w-8 h-8 rounded-[10px] bg-primary-soft flex items-center justify-center shrink-0">
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
            <x-section-header :title="__('imports.pdf.history_title')" :description="__('imports.pdf.history_description')" icon="fa-clock-rotate-left" />

            <div class="mb-4">
                <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-csv text-[10px] mr-1"></i> CSV</p>
                @if($csvHistory->isEmpty())
                    <p class="text-small text-tertiary italic">{{ __('imports.pdf.empty_csv_history') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach($csvHistory->take(5) as $h)
                            <li class="p-3 rounded-[12px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-body text-text font-medium truncate flex-1" title="{{ $h->nama_fail }}">{{ $h->nama_fail }}</p>
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
            </div>

            <div class="pt-4 border-t border-divider">
                <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-pdf text-[10px] mr-1"></i> PDF / OCR</p>
                @if($history->isEmpty())
                    <p class="text-small text-tertiary italic">{{ __('imports.pdf.empty_pdf_history') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach($history->take(5) as $h)
                            <li class="p-3 rounded-[12px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-body text-text font-medium truncate flex-1" title="{{ $h->original_filename }}">{{ $h->original_filename }}</p>
                                    @php
                                        $statusTone = match($h->status) {
                                            'extracted', 'needs_review' => 'success',
                                            'committed' => 'success',
                                            'failed' => 'danger',
                                            'processing' => 'warning',
                                            default => 'warning',
                                        };
                                        $statusLabel = match($h->status) {
                                            'extracted' => __('imports.status.extracted'),
                                            'needs_review' => __('imports.status.needs_review'),
                                            'committed' => __('imports.status.committed'),
                                            'failed' => __('imports.status.failed'),
                                            'processing' => __('imports.status.processing'),
                                            default => $h->status,
                                        };
                                    @endphp
                                    <x-status-badge :status="$statusTone" size="sm">
                                        {{ $statusLabel }}
                                    </x-status-badge>
                                </div>
                                <p class="text-eyebrow text-tertiary mt-1.5 tabular-nums">{{ $h->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-3 border-t border-divider">
                        <x-app-button variant="ghost" size="sm" block icon="fa-arrow-right" href="{{ route('import-pdf.history') }}">{{ __('imports.csv.view_all') }}</x-app-button>
                    </div>
                @endif
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header :title="__('imports.pdf.stats_title')" icon="fa-chart-simple" />

            <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-pdf text-[10px] mr-1"></i> PDF / OCR</p>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $stats['total'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.total') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $stats['committed'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.committed') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-warning tabular-nums">{{ $stats['extracted'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.extracted') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $stats['failed'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.failed') }}</p>
                </div>
            </div>

            <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2 pt-4 border-t border-divider"><i class="fa-solid fa-file-csv text-[10px] mr-1"></i> CSV</p>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $csvStats['total'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.pdf.records_label') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $csvStats['berjaya'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('common.status.success') }}</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $csvStats['gagal'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ __('imports.status.failed') }}</p>
                </div>
            </div>
        </x-app-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ocrScanner', () => ({
            dragging: false,
            fileName: '',
            fileSize: 0,
            documentType: 'borang_bantuan_maij',
            uploading: false,
            progress: 0,
            stage: 'queued',
            scanId: null,
            statusUrl: null,
            reviewUrl: null,
            pollTimer: null,
            stalled: false,
            failed: false,
            errorMessage: '',
            healthWarning: '',

            stageDefs: [
                { pct: 10, label: @js(__('imports.pdf.stage_start')) },
                { pct: 25, label: @js(__('imports.pdf.stage_analyze')) },
                { pct: 30, label: @js(__('imports.pdf.stage_extract')) },
                { pct: 70, label: @js(__('imports.pdf.stage_text')) },
                { pct: 85, label: @js(__('imports.pdf.stage_fields')) },
                { pct: 100, label: @js(__('imports.pdf.stage_done')) },
            ],

            init() {
                this.checkHealth();
                window.addEventListener('pageshow', (e) => {
                    if (e.persisted) { this.uploading = false; }
                });
            },

            get stageLabel() {
                const stages = {
                    queued: @js(__('imports.pdf.stage_statuses.queued')),
                    started: @js(__('imports.pdf.stage_statuses.started')),
                    analyzing: @js(__('imports.pdf.stage_statuses.analyzing')),
                    extracting: @js(__('imports.pdf.stage_statuses.extracting')),
                    extracted: @js(__('imports.pdf.stage_statuses.extracted')),
                    recognizing: @js(__('imports.pdf.stage_statuses.recognizing')),
                    done: @js(__('imports.pdf.stage_statuses.done')),
                    failed: @js(__('imports.pdf.stage_statuses.failed')),
                };
                return stages[this.stage] || this.stage;
            },

            get processingHint() {
                return @js(__('imports.pdf.processing_hint'));
            },

            checkHealth() {
                fetch('{{ route('import-pdf.preflight') }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.any_engine_ready) {
                            this.healthWarning = @js(__('imports.pdf.health_no_engine'));
                        } else if (d.stalled_count > 0) {
                            this.healthWarning = @js(__('imports.pdf.health_stalled')).replace(':count', d.stalled_count);
                        } else if (d.pending_queue > 0) {
                            this.healthWarning = @js(__('imports.pdf.health_pending')).replace(':count', d.pending_queue);
                        }
                    })
                    .catch(() => {});
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.fileSize = file.size;
                    this.failed = false;
                    this.errorMessage = '';
                }
            },

            handleDrop(event) {
                this.dragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.fileName = file.name;
                    this.fileSize = file.size;
                    this.failed = false;
                    this.errorMessage = '';
                }
            },

            clearFile() {
                this.fileName = '';
                this.fileSize = 0;
                this.$refs.fileInput.value = '';
            },

            submitUpload() {
                if (!this.fileName || this.uploading) return;
                this.uploading = true;
                this.progress = 5;
                this.stage = 'queued';
                this.failed = false;
                this.stalled = false;
                this.errorMessage = '';

                const fd = new FormData();
                fd.append('file', this.$refs.fileInput.files[0]);
                fd.append('document_type', this.documentType);
                fd.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('import-pdf.upload') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        if (data && data.scan_id) {
                            this.scanId = data.scan_id;
                            this.statusUrl = data.status_url;
                            this.reviewUrl = data.review_url;
                            if (data.fast_path) {
                                this.progress = 100;
                                this.stage = 'done';
                                this.uploading = false;
                                setTimeout(() => window.location.assign(data.review_url), 600);
                                return;
                            }
                            this.startPolling();
                        } else if (data && data.review_url) {
                            this.uploading = false;
                            window.location.assign(data.review_url);
                        }
                    })
                    .catch(() => {
                        this.uploading = false;
                        this.failed = true;
                        this.errorMessage = @js(__('imports.pdf.upload_failed'));
                    });
            },

            startPolling() {
                this.stopPolling();
                this.pollTimer = setInterval(() => this.pollStatus(), 1500);
            },

            pollStatus() {
                if (!this.scanId) return;
                fetch(this.statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(r => r.json())
                    .then(d => {
                        this.progress = Math.max(this.progress, d.progress || 0);
                        this.stage = d.stage || 'queued';
                        if (d.stalled) { this.stalled = true; }
                        if (['extracted', 'needs_review', 'committed'].includes(d.status)) {
                            this.progress = 100;
                            this.stage = 'done';
                            this.uploading = false;
                            this.stopPolling();
                            setTimeout(() => window.location.assign(this.reviewUrl), 500);
                        } else if (d.status === 'failed') {
                            this.uploading = false;
                            this.failed = true;
                            this.errorMessage = d.error_message || @js(__('imports.pdf.processing_failed'));
                            this.stopPolling();
                        }
                    })
                    .catch(() => {});
            },

            stopPolling() {
                if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null; }
            },

            formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(2) + ' MB';
            }
        }));
    });
</script>
@endpush
