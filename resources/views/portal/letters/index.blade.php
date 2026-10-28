@extends('portal.layouts.app')
@section('title', __('portal_documents.letters.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_documents.letters.title'))

@section('content')
<x-page-header
    title="{{ __('portal_documents.letters.title') }}"
    subtitle="{{ __('portal_documents.letters.subtitle') }}"
/>

@if($letters->isEmpty())
    <x-app-card>
        <x-empty-state icon="fa-envelope-open-text" title="{{ __('portal_documents.letters.empty.title') }}" description="{{ __('portal_documents.letters.empty.description') }}" />
    </x-app-card>
@else
    <div class="space-y-3">
        @foreach($letters as $item)
            @php $app = $item['application']; @endphp
            <x-app-card class="p-5" hover>
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-[12px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-pdf text-[16px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-body text-text font-semibold">{{ $app->application_no }}</p>
                                <p class="text-small text-secondary mt-0.5">
                                    {{ $app->assistance_type ?: __('portal_documents.letters.fallback_assistance') }}
                                    · {{ $app->submitted_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?: $app->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <x-status-badge :status="match($app->status) { 'approved' => 'success', 'rejected' => 'danger', 'under_review' => 'info', default => 'warning' }">
                                {{ match($app->status) { 'approved' => __('portal_documents.letters.status.approved'), 'rejected' => __('portal_documents.letters.status.rejected'), 'under_review' => __('portal_documents.letters.status.under_review'), 'submitted' => __('portal_documents.letters.status.submitted'), default => ucfirst($app->status) } }}
                            </x-status-badge>
                        </div>
                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            @if($item['has_letter'])
                                <a href="{{ $item['letter_url'] }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                    {{ $item['letter_type'] === 'approved' ? __('portal_documents.letters.approved_letter') : __('portal_documents.letters.rejection_notice') }}
                                </a>
                            @endif
                            <a href="{{ $item['receipt_url'] }}" class="btn btn-sm btn-outline">
                                <i class="fa-solid fa-receipt text-[11px]"></i> {{ __('portal_documents.letters.receipt') }}
                            </a>
                        </div>
                    </div>
                </div>
            </x-app-card>
        @endforeach
    </div>
@endif
@endsection
