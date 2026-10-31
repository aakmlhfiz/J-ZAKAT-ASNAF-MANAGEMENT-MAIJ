@extends('portal.layouts.app')
@section('title', __('document_ocr.review.meta_title'))
@section('page-title', __('document_ocr.review.title'))

@section('content')
    <x-page-header
        :title="__('document_ocr.review.title')"
        :subtitle="$import->original_filename"
        :eyebrow="$type->label()"
        icon="scan-line"
    >
        <x-slot:actions>
            <a href="{{ route('user.documents.index') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left text-[11px]"></i> {{ __('document_ocr.review.back') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('partials.document-ocr-review', [
        'import' => $import,
        'type' => $type,
        'groups' => $groups,
        'confidence' => $confidence,
        'previewPageUrl' => $previewPageUrl,
        'verifyRoute' => $verifyRoute,
        'fileRoute' => $fileRoute,
        'backRoute' => $backRoute,
        'isAdmin' => false,
        'replacementRoute' => route('user.documents.replace', $import->id),
    ])
@endsection
