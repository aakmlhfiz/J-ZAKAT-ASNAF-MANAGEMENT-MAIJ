@extends('portal.layouts.app')
@section('title', __('portal_documents.documents.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_documents.documents.title'))

@section('content')
<x-page-header
    title="{{ __('portal_documents.documents.title') }}"
    subtitle="{{ __('portal_documents.documents.subtitle') }}"
/>

{{-- Upload --}}
<x-app-card class="p-6 mb-6" :hover="false">
    <x-section-header :title="__('portal_documents.documents.upload.title')" :description="__('portal_documents.documents.upload.description')" icon="fa-cloud-arrow-up" />
    <form method="POST" action="{{ route('user.documents.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_documents.documents.upload.type') }} <span class="text-danger">*</span></label>
                <select name="document_type" class="input-base w-full">
                    @foreach(App\Services\PortalDocumentService::typeOptions() as $key => $label)
                        <option value="{{ $key }}" {{ old('document_type') === $key ? 'selected' : '' }}>{{ Lang::has('portal_documents.documents.types.'.$key) ? __('portal_documents.documents.types.'.$key) : $label }}</option>
                    @endforeach
                </select>
                @error('document_type') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_documents.documents.upload.file') }} <span class="text-danger">*</span></label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="input-base w-full">
                @error('file') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-4">
            <x-app-button type="submit" variant="primary" size="md" icon="fa-cloud-arrow-up">{{ __('portal_documents.documents.upload.submit') }}</x-app-button>
        </div>
    </form>
</x-app-card>

{{-- List --}}
@if($documents->count() === 0)
    <x-app-card>
        <x-empty-state icon="fa-folder-open" title="{{ __('portal_documents.documents.empty.title') }}" description="{{ __('portal_documents.documents.empty.description') }}" />
    </x-app-card>
@else
    <div class="space-y-3">
        @foreach($documents as $doc)
            @php
                $statusMap = [
                    'uploaded'     => ['label' => __('portal_documents.documents.status.uploaded'),     'tone' => 'info'],
                    'processing'   => ['label' => __('portal_documents.documents.status.processing'),   'tone' => 'warning'],
                    'extracted'    => ['label' => __('portal_documents.documents.status.extracted'),    'tone' => 'info'],
                    'needs_review' => ['label' => __('portal_documents.documents.status.needs_review'), 'tone' => 'warning'],
                    'committed'    => ['label' => __('portal_documents.documents.status.committed'),    'tone' => 'success'],
                    'failed'       => ['label' => __('portal_documents.documents.status.failed'),       'tone' => 'danger'],
                ];
                $meta = $statusMap[$doc->status] ?? ['label' => ucfirst($doc->status), 'tone' => 'secondary'];
                $isBorang = $doc->document_type === App\Services\Ocr\OcrService::DOC_TYPE_BORANG_BANTUAN;
            @endphp
            <x-app-card class="p-5" hover>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-[12px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $isBorang ? 'fa-file-contract' : 'fa-file-lines' }} text-[16px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-body text-text font-semibold truncate">{{ $doc->original_filename }}</p>
                                <p class="text-small text-secondary mt-0.5">{{ $doc->template ?: __('portal_documents.documents.fallback_type') }} · {{ $doc->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</p>
                                @if($doc->overall_confidence !== null)
                                    <p class="text-eyebrow text-tertiary mt-1">{{ __('portal_documents.documents.ocr_confidence', ['value' => number_format($doc->overall_confidence, 1)]) }}</p>
                                @endif
                            </div>
                            <x-status-badge :status="$meta['tone']">{{ $meta['label'] }}</x-status-badge>
                        </div>
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <a href="{{ route('user.documents.download', $doc->id) }}" class="btn btn-sm btn-outline">
                                <i class="fa-solid fa-download text-[11px]"></i> {{ __('portal_documents.documents.download') }}
                            </a>
                            @if($isBorang && !in_array($doc->status, ['committed']))
                                @if(in_array($doc->status, ['extracted', 'needs_review', 'failed']))
                                <a href="{{ route('user.applications.scan.preview', $doc->id) }}" class="btn btn-sm btn-outline">
                                    <i class="fa-solid fa-eye text-[11px]"></i> {{ __('portal_documents.documents.review') }}
                                </a>
                                @endif
                                <form method="POST" action="{{ route('user.documents.reprocess', $doc->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-ghost">
                                        <i class="fa-solid fa-rotate text-[11px]"></i> {{ __('portal_documents.documents.reprocess') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </x-app-card>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $documents->links() }}
    </div>
@endif
@endsection
