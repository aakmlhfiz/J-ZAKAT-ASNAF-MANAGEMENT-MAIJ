@extends('portal.layouts.app')
@section('title', 'Imbas Borang Bantuan MAIJ — Portal Pemohon')
@section('page-title', 'Imbas Borang Bantuan MAIJ')

@section('content')
@php $user = auth('portal')->user(); @endphp

<div class="max-w-4xl">
    <div class="mb-6">
        <h1 class="text-title text-text">Imbas Borang Bantuan MAIJ</h1>
        <p class="text-body text-secondary mt-1">
            Muat naik Borang Agihan Zakat rasmi MAIJ (PDF, JPG atau PNG). Sistem AI akan mengekstrak maklumat secara automatik, menjana skor kelayakan, dan menghantar permohonan untuk semakan.
        </p>
    </div>

    <x-app-card class="p-6 mb-5" :hover="false">
        <div class="flex items-start gap-3 mb-5 p-4 rounded-[14px] bg-primary-soft/40 border border-primary/10">
            <i class="fa-solid fa-circle-info text-primary text-[14px] mt-0.5"></i>
            <div class="text-small text-text leading-relaxed">
                <p class="font-semibold mb-1">Bagaimana ia berfungsi?</p>
                <ol class="list-decimal ml-4 space-y-0.5 text-secondary">
                    <li>Muat naik Borang Bantuan MAIJ (PDF, JPG atau PNG)</li>
                    <li>Sistem mengekstrak teks menggunakan OCR + AI</li>
                    <li>AI mengecam medan termasuk seksyen 5 (Maklumat Tambahan), 8 (Harta), 10 (Pendapatan & Perbelanjaan)</li>
                    <li>Semak & sahkan maklumat yang diekstrak</li>
                    <li>Sistem menjalankan penilaian Had Kifayah & menjana Skor AI</li>
                    <li>Permohonan dihantar untuk semakan pentadbir</li>
                </ol>
            </div>
        </div>

        <form method="POST" action="{{ route('user.applications.scan.upload') }}" enctype="multipart/form-data" x-data="scanUploader()">
            @csrf

            <div @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()"
                 class="relative border-2 border-dashed rounded-[18px] p-10 text-center cursor-pointer transition-apple"
                 :class="dragging ? 'border-primary bg-primary-soft scale-[1.01]' : 'border-divider hover:border-primary/50 hover:bg-primary-soft/30'">

                <input type="file" name="file" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden"
                       @change="handleFileSelect($event)" required>

                <div class="w-14 h-14 rounded-[16px] bg-primary-soft mx-auto flex items-center justify-center mb-4 ring-1 ring-primary/15">
                    <i class="fa-solid fa-file-contract text-primary text-[24px]"></i>
                </div>

                <p class="text-body text-text font-medium" x-text="fileName || 'Tarik Borang Bantuan MAIJ ke sini atau klik untuk pilih'"></p>
                <p class="text-small text-secondary mt-2">Format disokong: PDF, JPG, PNG · Saiz maksimum: 10MB</p>

                <div x-show="fileName" x-cloak class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-pill bg-muted">
                    <i class="fa-solid fa-file text-primary text-[12px]"></i>
                    <span class="text-small text-text font-medium" x-text="fileName"></span>
                    <span x-show="fileSize" class="text-eyebrow text-tertiary" x-text="formatSize(fileSize)"></span>
                    <button @click.stop="clearFile()" type="button" class="w-5 h-5 rounded-full hover:bg-danger/10 flex items-center justify-center text-tertiary hover:text-danger transition-fast">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>
            </div>

            @error('file')
                <p class="text-small text-danger mt-3">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-3 mt-5">
                <button type="submit" class="btn btn-primary" :disabled="!fileName || uploading" @click="startUpload()">
                    <template x-if="!uploading">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-magic-wand-sparkles text-[14px]"></i>
                            <span>Imbas & Ekstrak Maklumat</span>
                        </span>
                    </template>
                    <template x-if="uploading">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin text-[14px]"></i>
                            <span>AI sedang memproses...</span>
                        </span>
                    </template>
                </button>
                <a href="{{ route('user.applications.index') }}" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left text-[13px]"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div x-show="uploading" x-cloak class="mt-4 p-4 rounded-[14px] bg-primary-soft/40 border border-primary/15 text-small text-text">
                <i class="fa-solid fa-circle-info text-primary mr-2"></i>
                Pemprosesan boleh mengambil masa 10–60 saat. Jangan tutup tetingkap ini.
            </div>
            <div x-show="uploadTimeout" x-cloak class="mt-4 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 text-small text-text">
                <i class="fa-solid fa-triangle-exclamation text-warning mr-2"></i>
                Pengesahan mengambil masa lebih lama daripada biasa. Anda boleh klik Kembali dan pergi — pemprosesan akan diteruskan di latar belakang.
            </div>
        </form>
    </x-app-card>

    @if($recent->isNotEmpty())
        <x-app-card class="p-6" :hover="false">
            <h2 class="text-body font-semibold text-text mb-4">Imbas Terakhir</h2>
            <ul class="space-y-2">
                @foreach($recent as $r)
                    <li class="flex items-center justify-between gap-3 p-3 rounded-[12px] hover:bg-muted transition-apple">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-[10px] bg-primary-soft flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-file-contract text-primary text-[13px]"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-small font-medium text-text truncate">{{ $r->original_filename }}</p>
                                <p class="text-eyebrow text-tertiary mt-0.5">{{ $r->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @php
                                $statusMap = [
                                    'processing' => ['label' => 'Memproses', 'tone' => 'warning'],
                                    'extracted' => ['label' => 'Diekstrak', 'tone' => 'success'],
                                    'needs_review' => ['label' => 'Perlu Semakan', 'tone' => 'warning'],
                                    'committed' => ['label' => 'Dihantar', 'tone' => 'success'],
                                    'failed' => ['label' => 'Gagal', 'tone' => 'danger'],
                                ];
                                $meta = $statusMap[$r->status] ?? ['label' => $r->status, 'tone' => 'secondary'];
                            @endphp
                            <x-status-badge :status="$meta['tone']" size="sm">{{ $meta['label'] }}</x-status-badge>
                            <a href="{{ route('user.applications.scan.preview', $r->id) }}" class="btn btn-sm btn-ghost">
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-app-card>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scanUploader', () => ({
            dragging: false,
            fileName: '',
            fileSize: 0,
            uploading: false,
            uploadTimeout: false,
            timeoutId: null,

            init() {
                window.addEventListener('pageshow', (e) => {
                    if (e.persisted) {
                        this.uploading = false;
                        this.clearUploadTimeout();
                    }
                });
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        this.uploading = false;
                        this.clearUploadTimeout();
                    }
                });
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.fileName = file.name;
                    this.fileSize = file.size;
                }
            },

            handleDrop(event) {
                this.dragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.fileName = file.name;
                    this.fileSize = file.size;
                }
            },

            clearFile() {
                this.fileName = '';
                this.fileSize = 0;
                this.$refs.fileInput.value = '';
            },

            clearUploadTimeout() {
                if (this.timeoutId) {
                    clearTimeout(this.timeoutId);
                    this.timeoutId = null;
                }
            },

            startUpload() {
                if (this.fileName && !this.uploading) {
                    this.uploading = true;
                    this.uploadTimeout = false;
                    this.clearUploadTimeout();
                    this.timeoutId = setTimeout(() => {
                        this.uploading = false;
                        this.uploadTimeout = true;
                    }, 90000);
                }
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
