@extends('portal.layouts.app')
@section('title', __('portal_documents.documents.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_documents.documents.title'))

@section('content')
<x-page-header
    title="{{ __('portal_documents.documents.title') }}"
    subtitle="{{ __('portal_documents.documents.subtitle') }}"
/>

<x-app-card class="p-6 mb-6" :hover="false">
    <x-section-header :title="__('portal_documents.documents.upload.title')" :description="__('portal_documents.documents.upload.description')" icon="cloud-arrow-up" />

    <div
        x-data="documentUploader({
            action: @js(route('user.documents.store')),
            statusUrlTemplate: @js(route('user.documents.status', '__ID__')),
        })"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="handleDrop($event)"
        class="space-y-4"
    >
        <form method="POST" action="{{ route('user.documents.store') }}" enctype="multipart/form-data" @submit.prevent="submit()">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_documents.documents.upload.type') }} <span class="text-danger">*</span></label>
                    <select name="document_type" x-model="documentType" class="input-base w-full">
                        @foreach(App\Enums\DocumentType::portalOptions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-small font-medium text-text mb-1.5">{{ __('document_ocr.admin.applicant') }} ({{ __('portal_documents.documents.upload.optional') ?? 'optional' }})</label>
                    <select name="application_id" x-model="applicationId" class="input-base w-full">
                        <option value="">—</option>
                        @foreach($applications as $application)
                            <option value="{{ $application->id }}">{{ $application->application_no }} · {{ $application->status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <div @click="$refs.fileInput.click()"
                     class="relative border-2 border-dashed rounded-[10px] p-8 text-center cursor-pointer transition-fast"
                     :class="dragging ? 'border-primary bg-primary-soft/40' : 'border-divider hover:border-primary/50 hover:bg-primary-soft/20'">
                    <input type="file" name="file" x-ref="fileInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="handleFileSelect($event)">
                    <div class="w-12 h-12 rounded-[8px] bg-primary-soft text-primary mx-auto flex items-center justify-center mb-3">
                        <i class="fa-solid fa-cloud-arrow-up text-[20px]"></i>
                    </div>
                    <p class="text-small font-semibold text-text" x-text="fileName || @js(__('imports.pdf.drop_title'))"></p>
                    <p class="text-[11px] text-tertiary mt-1">{{ __('imports.pdf.formats') }} · {{ __('imports.pdf.max_size') }}</p>

                    <div x-show="fileName" x-cloak class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] bg-muted">
                        <i class="fa-solid fa-file text-primary text-[12px]"></i>
                        <span class="text-small text-text" x-text="fileName"></span>
                        <span class="text-[10px] text-tertiary" x-text="formatSize(fileSize)"></span>
                        <button type="button" @click.stop="clearFile()" class="text-tertiary hover:text-danger" aria-label="{{ __('imports.pdf.remove') ?? 'Remove' }}"><i class="fa-solid fa-xmark text-[10px]"></i></button>
                    </div>
                </div>
            </div>

            <div x-show="uploading || processing" x-cloak class="mt-4">
                <div class="flex items-center justify-between text-[11px] text-secondary mb-1">
                    <span x-text="stageLabel"></span>
                    <span x-text="progress + '%'" class="tabular-nums"></span>
                </div>
                <div class="h-2 bg-muted rounded-[4px] overflow-hidden">
                    <div class="h-full bg-primary transition-all duration-300" :style="`width: ${progress}%`"></div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3 flex-wrap">
                <button type="submit" class="btn btn-primary" :disabled="!fileName || uploading || processing">
                    <i class="fa-solid fa-upload text-[12px]" x-show="!uploading && !processing"></i>
                    <i class="fa-solid fa-spinner fa-spin text-[12px]" x-show="uploading || processing"></i>
                    {{ __('portal_documents.documents.upload.submit') }}
                </button>
                <p class="text-[11px] text-tertiary">{{ __('document_ocr.review.masked_hint') }}</p>
            </div>

            <div x-show="error" x-cloak class="mt-3 p-3 rounded-[8px] bg-danger-soft border border-danger/20 text-small text-danger" x-text="error"></div>
        </form>
    </div>
</x-app-card>

@if($documents->count() === 0)
    <x-app-card>
        <x-empty-state icon="folder" title="{{ __('portal_documents.documents.empty.title') }}" description="{{ __('portal_documents.documents.empty.description') }}" />
    </x-app-card>
@else
    <div class="space-y-3">
        @foreach($documents as $doc)
            @php
                $isBorang = $doc->type()->isBorang();
                $canReview = ! $isBorang && in_array($doc->status, ['needs_review', 'extracted', 'committed', 'failed', 'rejected'], true);
                $reviewUrl = $isBorang
                    ? route('user.applications.scan.preview', $doc->id)
                    : route('user.documents.review', $doc->id);
            @endphp
            <x-app-card class="p-4" hover>
                <div class="flex items-start gap-4" @if($doc->status === 'processing') x-data="documentRowPoller('{{ route('user.documents.status', $doc->id) }}')" x-init="start()" @endif>
                    <div class="w-10 h-10 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $isBorang ? 'fa-file-contract' : 'fa-file-lines' }} text-[15px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-small text-text font-semibold truncate">{{ $doc->original_filename }}</p>
                                <p class="text-[11px] text-secondary mt-0.5">{{ $doc->type()->label() }} · {{ $doc->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</p>
                                @if($doc->overall_confidence !== null)
                                    <p class="text-[10px] text-tertiary mt-1">{{ __('portal_documents.documents.ocr_confidence', ['value' => number_format((float) $doc->overall_confidence, 1)]) }}</p>
                                @endif
                                @if($doc->status === 'processing')
                                    <div class="mt-2 max-w-xs">
                                        <div class="h-1.5 bg-muted rounded-[4px] overflow-hidden">
                                            <div class="h-full bg-primary transition-all" :style="`width: ${progress}%`"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <x-status-badge :status="$doc->statusTone()">{{ $doc->statusLabel() }}</x-status-badge>
                        </div>
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <a href="{{ route('user.documents.download', $doc->id) }}" class="btn btn-sm btn-outline">
                                <i class="fa-solid fa-download text-[10px]"></i> {{ __('portal_documents.documents.download') }}
                            </a>
                            @if($canReview || $isBorang)
                                <a href="{{ $reviewUrl }}" class="btn btn-sm btn-outline">
                                    <i class="fa-solid fa-eye text-[10px]"></i> {{ __('portal_documents.documents.review') }}
                                </a>
                            @endif
                            @if(! in_array($doc->status, ['committed', 'processing'], true))
                                <form method="POST" action="{{ route('user.documents.reprocess', $doc->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-ghost">
                                        <i class="fa-solid fa-rotate text-[10px]"></i> {{ __('portal_documents.documents.reprocess') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </x-app-card>
        @endforeach
    </div>

    <div class="mt-6">{{ $documents->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('documentUploader', (config) => ({
            dragging: false,
            fileName: '',
            fileSize: 0,
            documentType: 'mykad',
            applicationId: '',
            uploading: false,
            processing: false,
            progress: 0,
            stageLabel: '',
            error: '',

            handleFileSelect(event) {
                const file = event.target.files[0] || null;
                this.setFile(file);
            },

            handleDrop(event) {
                this.dragging = false;
                const file = event.dataTransfer.files[0] || null;
                if (file) {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                    this.setFile(file);
                }
            },

            setFile(file) {
                if (!file) return;
                this.fileName = file.name;
                this.fileSize = file.size;
                this.error = '';
            },

            clearFile() {
                this.fileName = '';
                this.fileSize = 0;
                this.progress = 0;
                this.error = '';
                this.$refs.fileInput.value = '';
            },

            formatSize(bytes) {
                if (!bytes) return '0 KB';
                return bytes > 1024 * 1024
                    ? (bytes / 1024 / 1024).toFixed(1) + ' MB'
                    : Math.round(bytes / 1024) + ' KB';
            },

            submit() {
                if (!this.fileName || this.uploading || this.processing) return;
                this.error = '';
                this.uploading = true;
                this.progress = 0;
                this.stageLabel = @js(__('portal_documents.documents.upload.uploading'));

                const form = this.$root.querySelector('form');
                const data = new FormData(form);
                data.set('document_type', this.documentType);
                data.set('application_id', this.applicationId);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', config.action, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.onprogress = (event) => {
                    if (!event.lengthComputable) return;
                    this.progress = Math.min(30, Math.round((event.loaded / event.total) * 30));
                };

                xhr.onload = () => {
                    this.uploading = false;
                    let body = {};
                    try { body = JSON.parse(xhr.responseText || '{}'); } catch (e) {}

                    if (xhr.status < 200 || xhr.status >= 300) {
                        this.error = body.message || @js(__('imports.pdf.processing_failed_retry'));
                        return;
                    }

                    if ((body.duplicate || body.ready) && body.review_url) {
                        window.location.href = body.review_url;
                        return;
                    }

                    this.processing = true;
                    this.stageLabel = @js(__('document_ocr.status.processing'));
                    this.poll(body.status_url, body.review_url);
                };

                xhr.onerror = () => {
                    this.uploading = false;
                    this.error = @js(__('imports.pdf.processing_failed_retry'));
                };

                xhr.send(data);
            },

            poll(statusUrl, reviewUrl) {
                const tick = async () => {
                    try {
                        const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await response.json();
                        this.progress = Math.max(5, Number(data.progress || 0));
                        this.stageLabel = data.status_label || @js(__('document_ocr.status.processing'));

                        if (data.status === 'needs_review' || data.status === 'extracted' || data.status === 'committed') {
                            window.location.href = reviewUrl;
                            return;
                        }

                        if (data.status === 'failed' || data.status === 'rejected') {
                            this.processing = false;
                            this.error = data.error || @js(__('imports.pdf.processing_failed_retry'));
                            return;
                        }
                    } catch (e) {
                        this.processing = false;
                        this.error = @js(__('imports.pdf.processing_failed_retry'));
                        return;
                    }

                    window.setTimeout(tick, 1000);
                };

                window.setTimeout(tick, 1200);
            },
        }));

        Alpine.data('documentRowPoller', (url) => ({
            progress: 0,
            timer: null,
            start() {
                const tick = async () => {
                    try {
                        const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await response.json();
                        this.progress = Number(data.progress || 0);
                        if (!['processing', 'uploaded'].includes(data.status)) {
                            window.location.reload();
                            return;
                        }
                    } catch (e) {
                        return;
                    }
                    this.timer = window.setTimeout(tick, 1500);
                };
                this.timer = window.setTimeout(tick, 1500);
            },
            destroy() {
                if (this.timer) window.clearTimeout(this.timer);
            },
        }));
    });
</script>
@endpush
