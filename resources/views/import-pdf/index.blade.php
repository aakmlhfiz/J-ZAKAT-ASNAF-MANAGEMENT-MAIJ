@extends('layouts.app', [
    'title' => 'Import PDF / OCR',
    'subtitle' => 'Muat naik borang permohonan & AI pengekstrakan pintar',
])
@section('content')

<x-page-header title="Import PDF / OCR" subtitle="Muat naik borang permohonan (PDF, JPG, PNG) & AI akan mengekstrak maklumat secara automatik" eyebrow="Pemprosesan Dokumen AI" icon="fa-file-pdf">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.template') }}">Muat Turun Borang Kosong</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-clock-rotate-left" href="{{ route('import-pdf.history') }}">Sejarah</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-app-card class="p-6 mb-6" :hover="false" elevated>
    <x-stepper :steps="['Muat Naik', 'OCR Pemprosesan', 'Pratinjau & Semakan', 'Pengesahan AI', 'Pengesahan', 'Diimport']" :current="1" />
</x-app-card>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Import CSV (Pukal)" description="Muat naik data pemohon secara pukal melalui fail CSV" icon="fa-file-csv" />

            <form method="POST" action="{{ route('importcsv.store') }}" enctype="multipart/form-data">
                @csrf
                <x-file-dropzone name="csv" accept=".csv" label="Tarik fail CSV ke sini atau klik untuk muat naik" hint="Saiz maksimum: 10MB" />

                @error('csv')
                    <p class="text-eyebrow text-danger mt-3">{{ $message }}</p>
                @enderror

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
            <x-section-header title="Muat Naik Borang Permohonan" description="Pilih atau tarik fail borang permohonan (PDF, JPG, PNG) ke kawasan di bawah" icon="fa-cloud-arrow-up" />

            <div x-data="ocrScanner()" class="space-y-5" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)">
                <form id="upload-form" enctype="multipart/form-data" @submit.prevent="submitUpload()">

                    <div x-show="healthWarning" x-cloak class="mb-5 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 text-small text-text">
                        <i class="fa-solid fa-triangle-exclamation text-warning mr-2"></i>
                        <span x-text="healthWarning"></span>
                    </div>

                    <div class="mb-5">
                        <label for="document_type" class="block text-small font-semibold text-text mb-2">
                            <i class="fa-solid fa-file-circle-check text-primary text-[13px] mr-1.5"></i>
                            Jenis Borang
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="document_type" value="borang_bantuan_maij" class="peer sr-only" checked x-model="documentType">
                                <div class="border-2 border-divider rounded-[14px] p-4 peer-checked:border-primary peer-checked:bg-primary-soft/40 transition-apple hover:border-primary/50">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-[10px] bg-primary-soft flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-file-contract text-primary text-[14px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-small font-semibold text-text">Borang Bantuan MAIJ</p>
                                            <p class="text-eyebrow text-tertiary mt-1">Borang Agihan Zakat rasmi MAIJ (Bahagian 1-12)</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="document_type" value="generic" class="peer sr-only" x-model="documentType">
                                <div class="border-2 border-divider rounded-[14px] p-4 peer-checked:border-primary peer-checked:bg-primary-soft/40 transition-apple hover:border-primary/50">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-[10px] bg-muted flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-file-lines text-secondary text-[14px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-small font-semibold text-text">Borang Generik</p>
                                            <p class="text-eyebrow text-tertiary mt-1">Borang bukan rasmi / pemohon (OCR standard)</p>
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
                                    <span>Muat Naik & Ekstrak</span>
                                </span>
                            </template>
                            <template x-if="uploading">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-spinner fa-spin text-[14px]"></i>
                                    <span>AI sedang memproses...</span>
                                </span>
                            </template>
                        </button>
                        <x-app-button variant="outline" size="lg" icon="fa-clock-rotate-left" x-bind:disabled="uploading" href="{{ route('import-pdf.history') }}">Lihat Sejarah</x-app-button>
                    </div>

                    <div x-show="uploading" x-cloak class="mt-5 p-4 rounded-[14px] bg-primary-soft/40 border border-primary/15 text-small text-text">
                        <i class="fa-solid fa-circle-info text-primary mr-2"></i>
                        <span x-text="processingHint"></span>
                    </div>

                    <div x-show="stalled" x-cloak class="mt-5 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 text-small text-text">
                        <i class="fa-solid fa-triangle-exclamation text-warning mr-2"></i>
                        Pemprosesan mengambil masa lama. Semak enjin OCR atau pekerja queue.
                        <button type="button" class="ml-2 btn btn-warning btn-xs" @click="startPolling()">Cuba Semula</button>
                    </div>

                    <div x-show="failed" x-cloak class="mt-5 p-4 rounded-[14px] bg-danger-soft/40 border border-danger/20 text-small text-text">
                        <i class="fa-solid fa-circle-xmark text-danger mr-2"></i>
                        <span x-text="errorMessage || 'Pemprosesan gagal. Sila cuba lagi.'"></span>
                    </div>
                </form>

                <div class="soft-panel p-4 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
                    <div class="text-small text-text leading-relaxed">
                        <p class="font-semibold mb-1">Bagaimana ia berfungsi?</p>
                        <ol class="list-decimal ml-4 space-y-0.5 text-secondary">
                            <li>Muat naik borang MAIJ (PDF, JPG, atau PNG)</li>
                            <li>AI mengekstrak teks dari setiap halaman dokumen</li>
                            <li>Sistem mengesan & memetakan medan secara pintar</li>
                            <li>Semak keyakinan AI & data yang diekstrak dalam pratinjau</li>
                            <li>AI mengesahkan format IC, telefon, pendapatan, dan medan lain</li>
                            <li>Sahkan & simpan data ke dalam pangkalan data</li>
                        </ol>
                    </div>
                </div>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Medan Borang MAIJ yang Dikesan" description="Sistem akan mengecam medan berikut secara automatik dari borang permohonan MAIJ" icon="fa-magnifying-glass" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php
                    $detectedFields = [
                        ['label' => 'Nama Pemohon', 'aliases' => 'Nama Penuh, Full Name, Applicant Name', 'icon' => 'fa-user', 'section' => 'Pemohon'],
                        ['label' => 'No. IC', 'aliases' => 'No Kad Pengenalan, NRIC, MyKad', 'icon' => 'fa-id-card', 'section' => 'Pemohon'],
                        ['label' => 'Status Perkahwinan', 'aliases' => 'Marital Status, Berkahwin/Bujang', 'icon' => 'fa-ring', 'section' => 'Pemohon'],
                        ['label' => 'Merokok', 'aliases' => 'Smoking, Perokok', 'icon' => 'fa-smoking', 'section' => 'Pemohon'],
                        ['label' => 'No Telefon Bimbit', 'aliases' => 'Mobile, No. HP, Phone', 'icon' => 'fa-mobile-screen', 'section' => 'Hubungi'],
                        ['label' => 'No Telefon Rumah', 'aliases' => 'Home Phone, Telefon Rumah', 'icon' => 'fa-phone', 'section' => 'Hubungi'],
                        ['label' => 'Alamat', 'aliases' => 'Address, Alamat Surat Menyurat', 'icon' => 'fa-location-dot', 'section' => 'Alamat'],
                        ['label' => 'Daerah', 'aliases' => 'District', 'icon' => 'fa-map', 'section' => 'Alamat'],
                        ['label' => 'Poskod', 'aliases' => 'Postcode, Postal Code', 'icon' => 'fa-hashtag', 'section' => 'Alamat'],
                        ['label' => 'DUN / Parlimen', 'aliases' => 'State Constituency, Parliament', 'icon' => 'fa-landmark', 'section' => 'Alamat'],
                        ['label' => 'Kesihatan', 'aliases' => 'Health Condition, Penyakit', 'icon' => 'fa-heart-pulse', 'section' => 'Kesihatan'],
                        ['label' => 'Pendapatan', 'aliases' => 'Income, Monthly Salary, Gaji', 'icon' => 'fa-money-bill', 'section' => 'Kewangan'],
                        ['label' => 'Sewa / Utiliti', 'aliases' => 'Rent, Utilities', 'icon' => 'fa-house', 'section' => 'Kewangan'],
                        ['label' => 'Pengangkutan / Perubatan', 'aliases' => 'Transport, Medical Expenses', 'icon' => 'fa-car-side', 'section' => 'Kewangan'],
                        ['label' => 'Jenis Bantuan', 'aliases' => 'Sara Hidup, Perubatan, Berdikari...', 'icon' => 'fa-hand-holding-heart', 'section' => 'Bantuan'],
                        ['label' => 'Ahli Isi Rumah', 'aliases' => 'Household Members (Jadual)', 'icon' => 'fa-people-group', 'section' => 'Isi Rumah'],
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
            <x-section-header title="Sejarah Import" description="5 CSV + 5 PDF terkini" icon="fa-clock-rotate-left" />

            <div class="mb-4">
                <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-csv text-[10px] mr-1"></i> CSV</p>
                @if($csvHistory->isEmpty())
                    <p class="text-small text-tertiary italic">Belum ada import CSV.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($csvHistory->take(5) as $h)
                            <li class="p-3 rounded-[12px] hover:bg-muted transition-apple">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-body text-text font-medium truncate flex-1" title="{{ $h->nama_fail }}">{{ $h->nama_fail }}</p>
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
            </div>

            <div class="pt-4 border-t border-divider">
                <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-pdf text-[10px] mr-1"></i> PDF / OCR</p>
                @if($history->isEmpty())
                    <p class="text-small text-tertiary italic">Belum ada import PDF.</p>
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
                                    @endphp
                                    <x-status-badge :status="$statusTone" size="sm">
                                        {{ match($h->status) {
                                            'extracted' => 'Diekstrak',
                                            'needs_review' => 'Perlu Semakan',
                                            'committed' => 'Disimpan',
                                            'failed' => 'Gagal',
                                            'processing' => 'Memproses',
                                            default => $h->status,
                                        } }}
                                    </x-status-badge>
                                </div>
                                <p class="text-eyebrow text-tertiary mt-1.5 tabular-nums">{{ $h->created_at->format('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 pt-3 border-t border-divider">
                        <x-app-button variant="ghost" size="sm" block icon="fa-arrow-right" href="{{ route('import-pdf.history') }}">Lihat Semua</x-app-button>
                    </div>
                @endif
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header title="Statistik Import" icon="fa-chart-simple" />

            <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2"><i class="fa-solid fa-file-pdf text-[10px] mr-1"></i> PDF / OCR</p>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $stats['total'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Jumlah</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $stats['committed'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Disimpan</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-warning tabular-nums">{{ $stats['extracted'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Diekstrak</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $stats['failed'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Gagal</p>
                </div>
            </div>

            <p class="text-eyebrow text-tertiary uppercase tracking-wide font-semibold mb-2 pt-4 border-t border-divider"><i class="fa-solid fa-file-csv text-[10px] mr-1"></i> CSV</p>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-text tabular-nums">{{ $csvStats['total'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Rekod</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-success tabular-nums">{{ $csvStats['berjaya'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Berjaya</p>
                </div>
                <div class="text-center p-3 soft-panel">
                    <p class="text-metric-sm text-danger tabular-nums">{{ $csvStats['gagal'] }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">Gagal</p>
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
                { pct: 10, label: 'Mula' },
                { pct: 25, label: 'Analisis' },
                { pct: 30, label: 'Ekstrak' },
                { pct: 70, label: 'Teks' },
                { pct: 85, label: 'Medan' },
                { pct: 100, label: 'Siap' },
            ],

            init() {
                this.checkHealth();
                window.addEventListener('pageshow', (e) => {
                    if (e.persisted) { this.uploading = false; }
                });
            },

            get stageLabel() {
                const stages = { queued: 'Dalam Barisan', started: 'Mula Diproses', analyzing: 'Menganalisis Dokumen', extracting: 'Mengekstrak Teks', extracted: 'Teks Diekstrak', recognizing: 'Mengenali Medan', done: 'Selesai', failed: 'Gagal' };
                return stages[this.stage] || this.stage;
            },

            get processingHint() {
                return 'Pemprosesan boleh mengambil masa 10–60 saat. Jangan tutup tetingkap ini.';
            },

            checkHealth() {
                fetch('{{ route('import-pdf.preflight') }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.any_engine_ready) {
                            this.healthWarning = 'Semua enjin OCR tidak tersedia. Sila periksa pemasangan Tesseract/ImageMagick sebelum memuat naik.';
                        } else if (d.stalled_count > 0) {
                            this.healthWarning = `${d.stalled_count} kerja terdahulu tersekat. Sebaiknya proses semula sebelum muat naik baharu.`;
                        } else if (d.pending_queue > 0) {
                            this.healthWarning = `Barisan pemprosesan ada ${d.pending_queue} kerja. Muat naik mungkin mengambil masa lebih lama.`;
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
                        this.errorMessage = 'Gagal memuat naik fail.';
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
                            this.errorMessage = d.error_message || 'Pemprosesan gagal.';
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
