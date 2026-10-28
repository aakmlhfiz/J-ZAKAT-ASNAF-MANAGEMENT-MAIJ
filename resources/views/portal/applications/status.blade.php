@extends('portal.layouts.app')
@section('title', __('applications.tracking.title'))
@section('page-title', __('applications.tracking.page_title'))

@section('content')
<x-page-header
    title="{{ __('applications.tracking.heading') }}"
    subtitle="{{ __('applications.tracking.subtitle') }}"
/>

@if($applications->isEmpty())
    <x-app-card>
        <x-empty-state
            icon="fa-magnifying-glass-chart"
            title="{{ __('applications.tracking.empty_title') }}"
            description="{{ __('applications.tracking.empty_desc') }}"
        >
            <x-slot:action>
                <x-app-button variant="primary" size="md" icon="fa-plus" :href="route('user.applications.create')">
                    {{ __('applications.tracking.apply') }}
                </x-app-button>
            </x-slot:action>
        </x-empty-state>
    </x-app-card>
@else
    <div class="space-y-5">
        @foreach($applications as $app)
            @php
                $statusLabel = match($app->status) {
                    'draft' => __('applications.status.draft'),
                    'submitted' => __('applications.status.submitted'),
                    'under_review' => __('applications.status.under_review'),
                    'approved' => __('applications.status.approved'),
                    'rejected' => __('applications.status.rejected'),
                    'incomplete' => __('applications.status.incomplete'),
                    default => ucfirst($app->status),
                };
                $statusTone = match($app->status) {
                    'draft' => 'warning', 'submitted' => 'primary', 'under_review' => 'info',
                    'approved' => 'success', 'rejected' => 'danger', 'incomplete' => 'warning',
                    default => 'secondary',
                };
                $documentsRequested = \App\Models\ProfilAsnaf::where('ic', $app->ic_no)->whereNotNull('documents_requested_at')->exists();
            @endphp
            <x-app-card class="p-6" :hover="false">
                <div class="flex items-start justify-between gap-3 flex-wrap mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-primary-soft text-primary flex items-center justify-center">
                            <i class="fa-solid fa-file-lines text-[15px]"></i>
                        </div>
                        <div>
                            <p class="text-body text-text font-semibold">{{ $app->application_no }}</p>
                            <p class="text-small text-secondary">
                                {{ $app->assistance_type ?: __('applications.tracking.default_type') }}
                                · {{ $app->submitted_at?->locale(app()->getLocale())->translatedFormat('d M Y') ?: __('applications.status.not_submitted') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-status-badge :status="$statusTone">{{ $statusLabel }}</x-status-badge>
                        <a href="{{ route('user.applications.show', $app->id) }}" class="btn btn-sm btn-ghost" aria-label="{{ __('applications.tracking.view_details') }}">
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </a>
                    </div>
                </div>

                <x-application-timeline :application="$app" />

                @if(!empty($documentsRequested) && in_array($app->status, ['submitted', 'under_review']))
                    <div class="mt-5 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-warning mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-body font-semibold text-text">{{ __('applications.tracking.documents_requested_title') }}</p>
                            <p class="text-small text-secondary mt-0.5">{{ __('applications.tracking.documents_requested_desc') }}</p>
                            <a href="{{ route('user.documents.index') }}" class="btn btn-sm btn-primary mt-2">
                                <i class="fa-solid fa-cloud-arrow-up text-[11px]"></i> {{ __('applications.tracking.upload_documents') }}
                            </a>
                        </div>
                    </div>
                @endif
            </x-app-card>
        @endforeach
    </div>
@endif
@endsection
