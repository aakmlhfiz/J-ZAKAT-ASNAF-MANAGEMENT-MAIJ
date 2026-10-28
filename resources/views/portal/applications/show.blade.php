@extends('portal.layouts.app')
@section('title', __('applications.show.title'))
@section('page-title', __('applications.show.page_title'))

@section('content')
@php
    $user = auth('portal')->user();
    $statusLabel = match($application->status) {
        'draft' => __('applications.status.draft'),
        'submitted' => __('applications.status.submitted'),
        'under_review' => __('applications.status.under_review'),
        'approved' => __('applications.status.approved'),
        'rejected' => __('applications.status.rejected'),
        'incomplete' => __('applications.status.incomplete'),
        default => ucfirst($application->status),
    };
    $statusColor = match($application->status) {
        'draft' => 'warning',
        'submitted' => 'primary',
        'under_review' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'incomplete' => 'warning',
        default => 'secondary',
    };
    $maritalLabels = [
        'single' => __('applications.marital.single'),
        'married' => __('applications.marital.married'),
        'divorced' => __('applications.marital.divorced'),
        'widowed' => __('applications.marital.widowed'),
    ];
    $incomeSourceLabels = [
        'employment' => __('applications.income_source.employment'),
        'business' => __('applications.income_source.business'),
        'pension' => __('applications.income_source.pension'),
        'none' => __('applications.income_source.none'),
        'other' => __('applications.income_source.other'),
    ];
@endphp

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('user.applications.index') }}" class="w-9 h-9 rounded-lg hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-colors" aria-label="{{ __('applications.show.back') }}">
            <i class="fa-solid fa-arrow-left text-[14px]"></i>
        </a>
        <div>
            <h1 class="text-title text-text">{{ $application->application_no }}</h1>
            <p class="text-body text-secondary mt-1">{{ $application->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            @if($application->status === 'draft')
            <form method="POST" action="{{ route('user.applications.submit', $application->id) }}" onsubmit="return confirm(@js(__('applications.show.confirm_submit')))">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-paper-plane text-[12px]"></i>
                    <span>{{ __('applications.show.submit') }}</span>
                </button>
            </form>
            @endif
            @if(in_array($application->status, ['approved', 'rejected']))
                @php $letterProfil = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->first(); @endphp
                @if($application->status === 'approved')
                    <span class="inline-flex items-center gap-2 px-3 py-2 bg-success/10 text-success rounded-lg text-sm font-semibold">
                        <i class="fa-solid fa-check-circle"></i> {{ __('applications.status.approved') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-2 bg-danger/10 text-danger rounded-lg text-sm font-semibold">
                        <i class="fa-solid fa-xmark-circle"></i> {{ __('applications.status.rejected') }}
                    </span>
                @endif
                @if($letterProfil && $letterProfil->approved_at)
                    <span class="text-xs text-tertiary">{{ $letterProfil->approved_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</span>
                @endif
                @if($letterProfil && $letterProfil->letter_path)
                    <a href="{{ route('user.applications.letter', $application->id) }}" class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-file-pdf text-[13px]"></i> {{ __('applications.show.download_letter') }}
                    </a>
                @endif
            @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-semibold bg-{{ $statusColor }}-soft text-{{ $statusColor }}">
                {{ $statusLabel }}
            </span>
            @endif
            @if($application->submitted_at)
                <a href="{{ route('user.applications.receipt', $application->id) }}" class="btn btn-outline btn-sm inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt text-[12px]"></i> {{ __('applications.show.receipt') }}
                </a>
            @endif
        </div>
    </div>

    {{-- Progress Timeline --}}
    <x-app-card class="p-6 mb-5" :hover="false">
        <x-application-timeline :application="$application" />
    </x-app-card>

    @php $documentsRequested = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->whereNotNull('documents_requested_at')->exists(); @endphp
    @if(!empty($documentsRequested) && in_array($application->status, ['submitted', 'under_review']))
        <div class="mb-5 p-4 rounded-[14px] bg-warning-soft/40 border border-warning/20 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-warning mt-0.5"></i>
            <div class="flex-1">
                <p class="text-body font-semibold text-text">{{ __('applications.show.documents_requested_title') }}</p>
                <p class="text-small text-secondary mt-0.5">{{ __('applications.show.documents_requested_desc') }}</p>
                <a href="{{ route('user.documents.index') }}" class="btn btn-sm btn-primary mt-2">
                    <i class="fa-solid fa-cloud-arrow-up text-[11px]"></i> {{ __('applications.show.upload_documents') }}
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <x-app-card class="p-5">
            <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-tertiary text-[14px]"></i>
                {{ __('applications.show.applicant_info') }}
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.name') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->full_name }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.ic') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->ic_no }}</dd>
                </div>
                @if($application->date_of_birth)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.date_of_birth') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->date_of_birth->locale(app()->getLocale())->translatedFormat('d M Y') }}</dd>
                </div>
                @endif
                @if($application->gender)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.gender') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->gender === 'male' ? __('applications.gender.male') : __('applications.gender.female') }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.phone') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->phone }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.email') }}</dt>
                    <dd class="text-body text-text font-medium text-right truncate max-w-[180px]">{{ $application->email }}</dd>
                </div>
                @if($application->address)
                <div class="pt-3 border-t border-divider">
                    <dt class="text-small text-secondary mb-1">{{ __('applications.show.field.address') }}</dt>
                    <dd class="text-body text-text">{{ $application->address }}</dd>
                </div>
                @endif
                @if($application->district || $application->state)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.district_state') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->district }}{{ $application->district && $application->state ? ', ' : '' }}{{ $application->state }}</dd>
                </div>
                @endif
                @if($application->postcode)
                <div class="flex justify-between gap-2">
                    <dt class="text-small text-secondary">{{ __('applications.show.field.postcode') }}</dt>
                    <dd class="text-body text-text font-medium text-right">{{ $application->postcode }}</dd>
                </div>
                @endif
            </dl>
        </x-app-card>

        <div class="space-y-5">
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-people-roof text-tertiary text-[14px]"></i>
                    {{ __('applications.show.household_income') }}
                </h3>
                <dl class="space-y-3">
                    @if($application->marital_status)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">{{ __('applications.show.field.marital_status') }}</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $maritalLabels[$application->marital_status] ?? ucfirst($application->marital_status) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">{{ __('applications.show.field.households') }}</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $application->household_count }}</dd>
                    </div>
                    @if($application->occupation)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">{{ __('applications.show.field.occupation') }}</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $application->occupation }}</dd>
                    </div>
                    @endif
                    @if($application->income_source)
                    <div class="flex justify-between gap-2">
                        <dt class="text-small text-secondary">{{ __('applications.show.field.income_source') }}</dt>
                        <dd class="text-body text-text font-medium text-right">{{ $incomeSourceLabels[$application->income_source] ?? ucfirst($application->income_source) }}</dd>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-divider space-y-3">
                        <div class="flex justify-between gap-2">
                            <dt class="text-small text-secondary">{{ __('applications.show.field.monthly_income') }}</dt>
                            <dd class="text-body text-text font-semibold text-right">RM {{ number_format($application->monthly_income, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-small text-secondary">{{ __('applications.show.field.spouse_income') }}</dt>
                            <dd class="text-body text-text font-semibold text-right">RM {{ number_format($application->spouse_income, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-2 pt-2 border-t border-divider">
                            <dt class="text-body text-text font-semibold">{{ __('applications.show.field.total_income') }}</dt>
                            <dd class="text-body text-text font-bold text-right">RM {{ number_format($application->total_household_income, 2) }}</dd>
                        </div>
                    </div>
                </dl>
            </x-app-card>

            @if($application->had_kifayah_result)
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-tertiary text-[14px]"></i>
                    {{ __('applications.show.kifayah') }}
                </h3>
                <div class="text-center py-3">
                    <p class="text-metric-lg text-text">RM {{ number_format($application->had_kifayah_result, 2) }}</p>
                    @if($application->ai_score)
                    <p class="text-small text-secondary mt-1">{{ __('applications.show.ai_score', ['score' => $application->ai_score]) }}</p>
                    @endif
                </div>
            </x-app-card>
            @endif

            @if(in_array($application->status, ['approved', 'rejected']))
            @php $suratProfil = \App\Models\ProfilAsnaf::where('ic', $application->ic_no)->first(); @endphp
            <x-app-card class="p-5" :hover="false" :accent="$application->status === 'approved' ? 'success' : 'danger'">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-tertiary text-[14px]"></i>
                    {{ __('applications.show.letter_section') }}
                </h3>
                <div class="flex items-center gap-2 text-{{ $application->status === 'approved' ? 'success' : 'danger' }}">
                    <i class="fa-solid {{ $application->status === 'approved' ? 'fa-check-circle' : 'fa-xmark-circle' }} text-lg"></i>
                    <span class="text-sm font-semibold">{{ $application->status === 'approved' ? __('applications.show.approved_title') : __('applications.show.rejected_title') }}</span>
                </div>
                @if($suratProfil && $suratProfil->approved_at)
                    <p class="text-xs text-secondary mt-2">{{ __('applications.show.decided_on', ['date' => $suratProfil->approved_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A')]) }}</p>
                @endif
                @if($application->admin_remarks)
                    <div class="mt-3 soft-panel p-3 text-xs text-secondary">
                        <p class="text-eyebrow text-tertiary">{{ __('applications.show.admin_remarks') }}</p>
                        <p class="mt-1">{{ $application->admin_remarks }}</p>
                    </div>
                @endif
                @if($suratProfil && $suratProfil->letter_path)
                    <a href="{{ route('user.applications.letter', $application->id) }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-[10px] bg-primary text-white text-sm font-semibold hover:bg-primary/90 w-full justify-center">
                        <i class="fa-solid fa-file-pdf"></i>
                        {{ $application->status === 'approved' ? __('applications.show.download_approval_letter') : __('applications.show.download_rejection_notice') }}
                    </a>
                @endif
            </x-app-card>
            @endif

        </div>
    </div>
</div>
@endsection
