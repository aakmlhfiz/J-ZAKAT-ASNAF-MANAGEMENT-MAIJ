@extends('layouts.app', [
    'title' => __('document_ocr.review.meta_title'),
    'subtitle' => $import->original_filename,
])

@section('content')
    <x-page-header
        :title="__('document_ocr.review.title')"
        :subtitle="$import->original_filename"
        :eyebrow="$type->label()"
        icon="scan-line"
    >
        <x-slot:actions>
            <a href="{{ route('ocr.index') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left text-[11px]"></i> {{ __('document_ocr.admin.back_to_list') }}
            </a>
            @if($import->status !== 'committed')
                <form method="POST" action="{{ route('ocr.reprocess', $import->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-rotate text-[11px]"></i> {{ __('portal_documents.documents.reprocess') }}
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex items-center gap-3 flex-wrap text-small text-secondary">
        <span>{{ __('document_ocr.admin.uploaded_by') }}:
            <span class="text-text font-medium">{{ $import->portalUser?->name ?? $import->user?->name ?? '—' }}</span>
        </span>
        @if($import->profilAsnaf)
            <span>· {{ __('document_ocr.admin.applicant') }}:
                <a href="{{ route('viewprofile', $import->profilAsnaf->ic) }}" class="text-primary hover:underline">{{ $import->profilAsnaf->nama }}</a>
            </span>
        @endif
        <span>· {{ $import->created_at?->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</span>
    </div>

    @include('partials.document-ocr-review', [
        'import' => $import,
        'type' => $type,
        'groups' => $groups,
        'confidence' => $confidence,
        'previewPageUrl' => $previewPageUrl,
        'verifyRoute' => $verifyRoute,
        'rejectRoute' => $rejectRoute,
        'fileRoute' => $fileRoute,
        'backRoute' => $backRoute,
        'isAdmin' => true,
    ])
@endsection
