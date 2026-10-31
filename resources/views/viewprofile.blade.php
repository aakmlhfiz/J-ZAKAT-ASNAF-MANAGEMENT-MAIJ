@extends('layouts.app', [
    'title' => $profil->nama,
    'subtitle' => __('asnaf.show.subtitle', ['ic' => $profil->ic]),
])
@section('content')
@php
    $status = $profil->statusSlug();
    $statusTone = $profil->statusTone();
    $approvedAt = $profil->approved_at
        ? $profil->approved_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A')
        : null;
@endphp

<x-page-header
    :title="$profil->nama"
    :subtitle="__('asnaf.show.subtitle', ['ic' => $profil->ic])"
    eyebrow="{{ __('asnaf.show.eyebrow') }}"
    icon="fa-user">
    <x-slot:actions>
        @if ($profil->isPendingApproval())
            <form method="POST" action="{{ route('viewprofile.approve', $profil->ic) }}" class="inline">
                @csrf
                <x-app-button type="submit" variant="success" size="md" icon="fa-check">{{ __('asnaf.show.approval_approved') }}</x-app-button>
            </form>
            <x-app-button type="button" variant="destructive" size="md" icon="fa-xmark" onclick="document.getElementById('rejectModal').classList.remove('hidden')">{{ __('asnaf.show.reject') }}</x-app-button>
        @elseif ($profil->isApproved())
            <span class="inline-flex items-center gap-2 px-3 py-2 bg-success/10 text-success rounded-lg text-sm font-semibold"><i class="fa-solid fa-check-circle"></i> {{ __('asnaf.show.approved_badge') }}</span>
            @if ($profil->approvedBy)
                <span class="text-xs text-tertiary">{{ __('asnaf.show.by', ['name' => $profil->approvedBy->name, 'date' => $approvedAt]) }}</span>
            @endif
            @if ($profil->letter_path)
                <a href="{{ route('viewprofile.letter', $profil->ic) }}" class="btn btn-primary btn-sm inline-flex items-center gap-1.5"><i class="fa-solid fa-file-pdf text-[13px]"></i> {{ __('asnaf.show.download_letter') }}</a>
            @endif
        @elseif ($profil->isRejected())
            <span class="inline-flex items-center gap-2 px-3 py-2 bg-danger/10 text-danger rounded-lg text-sm font-semibold"><i class="fa-solid fa-xmark-circle"></i> {{ __('asnaf.show.rejected_badge') }}</span>
            @if ($profil->approvedBy)
                <span class="text-xs text-tertiary">{{ __('asnaf.show.by', ['name' => $profil->approvedBy->name, 'date' => $approvedAt]) }}</span>
            @endif
            @if ($profil->letter_path)
                <a href="{{ route('viewprofile.letter', $profil->ic) }}" class="btn btn-primary btn-sm inline-flex items-center gap-1.5"><i class="fa-solid fa-file-pdf text-[13px]"></i> {{ __('asnaf.show.download_letter') }}</a>
            @endif
        @endif
        <x-app-button variant="outline" size="md" icon="fa-print" onclick="window.print()">{{ __('asnaf.show.print') }}</x-app-button>
        <x-app-button variant="primary" size="md" icon="fa-pen" href="{{ route('profilasnaf.edit', $profil->ic) }}">{{ __('asnaf.show.update') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false" :accent="$statusTone">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-[12px] bg-primary text-white flex items-center justify-center text-[24px] font-semibold shrink-0">
                    {{ strtoupper(mb_substr($profil->nama, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-h3 text-text">{{ $profil->nama }}</h2>
                    <p class="text-small text-secondary mt-1">{{ __('asnaf.show.ic_label') }} <span class="text-text font-medium tabular-nums">{{ $profil->ic }}</span> · {{ __('asnaf.show.registered', ['time' => $profil->created_at->locale(app()->getLocale())->diffForHumans()]) }}</p>
                    <div class="mt-3">
                        <x-status-badge :status="$status" />
                    </div>
                </div>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="{{ __('asnaf.show.section_personal') }}" description="{{ __('asnaf.show.section_personal_description') }}" icon="fa-id-card" />
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.phone') }}</p><p class="text-body text-text font-medium mt-1.5 tabular-nums">{{ $profil->telefon ?: '—' }}</p></div>
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.relationship') }}</p><p class="text-body text-text font-medium mt-1.5">{{ $profil->hubungan ?: '—' }}</p></div>
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.head_ic') }}</p><p class="text-body text-text font-medium mt-1.5 tabular-nums">{{ $profil->ic_ketua_rumah ?: '—' }}</p></div>
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.income') }}</p><p class="text-body text-text font-semibold mt-1.5 tabular-nums">RM {{ number_format($profil->pendapatan, 2) }}</p></div>
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.dependents') }}</p><p class="text-body text-text font-medium mt-1.5">{{ __('asnaf.show.dependents_count', ['count' => $profil->bilangan_tanggungan]) }}</p></div>
                <div><p class="text-eyebrow text-tertiary">{{ __('asnaf.show.rent') }}</p><p class="text-body text-text font-medium mt-1.5 tabular-nums">RM {{ number_format($profil->sewa, 2) }}</p></div>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="{{ __('asnaf.show.section_address') }}" icon="fa-location-dot" />
            <div class="soft-panel p-4 text-body text-text">{{ $profil->alamat ?: __('asnaf.show.no_address') }}</div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="{{ __('asnaf.show.section_finance') }}" description="{{ __('asnaf.show.section_finance_description') }}" icon="fa-wallet" />
            <div class="space-y-3 text-body">
                <div class="flex items-center justify-between py-3 border-b border-divider"><span class="text-secondary">{{ __('asnaf.show.monthly_income') }}</span><span class="text-text font-semibold tabular-nums">RM {{ number_format($profil->pendapatan, 2) }}</span></div>
                <div class="flex items-center justify-between py-3 border-b border-divider"><span class="text-secondary">{{ __('asnaf.show.rent') }}</span><span class="text-text font-semibold tabular-nums">RM {{ number_format($profil->sewa, 2) }}</span></div>
                <div class="flex items-center justify-between py-3 border-b border-divider"><span class="text-secondary">{{ __('asnaf.show.utilities') }}</span><span class="text-text font-semibold tabular-nums">RM {{ number_format($profil->utiliti, 2) }}</span></div>
                <div class="flex items-center justify-between pt-3">
                    <span class="text-text font-semibold">{{ __('asnaf.show.kifayah') }}</span>
                    <span class="text-metric-sm text-text tabular-nums">RM {{ number_format($profil->had_kifayah, 2) }}</span>
                </div>
            </div>
        </x-app-card>
    </div>

    <div class="space-y-6">
        <x-app-card class="p-6" :hover="false" :accent="$profil->approvalStatusTone()">
            <p class="text-eyebrow text-tertiary">{{ __('asnaf.show.approval_eyebrow') }}</p>
            <h2 class="text-h3 text-text mt-1">{{ __('asnaf.approval_status.' . $profil->approvalStatusSlug()) }}</h2>
            @if ($profil->isPendingApproval())
                <p class="text-small text-secondary mt-2">{{ __('asnaf.show.approval_pending') }}</p>
            @elseif ($profil->isApproved())
                <div class="mt-3 flex items-center gap-2 text-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span class="text-sm font-medium">{{ __('asnaf.show.approval_approved') }}</span>
                </div>
                @if ($profil->approvedBy)
                    <p class="text-xs text-secondary mt-1">{{ __('asnaf.show.approval_approved_by', ['name' => $profil->approvedBy->name]) }}</p>
                    <p class="text-xs text-tertiary">{{ $approvedAt }}</p>
                @endif
            @elseif ($profil->isRejected())
                <div class="mt-3 flex items-center gap-2 text-danger">
                    <i class="fa-solid fa-xmark-circle"></i>
                    <span class="text-sm font-medium">{{ __('asnaf.show.approval_rejected') }}</span>
                </div>
                @if ($profil->approvedBy)
                    <p class="text-xs text-secondary mt-1">{{ __('asnaf.show.approval_rejected_by', ['name' => $profil->approvedBy->name]) }}</p>
                    <p class="text-xs text-tertiary">{{ $approvedAt }}</p>
                @endif
                @if ($profil->approval_remarks)
                    <div class="mt-3 soft-panel p-3 text-xs text-secondary">
                        <p class="text-eyebrow text-tertiary">{{ __('asnaf.show.remarks') }}</p>
                        <p class="mt-1">{{ $profil->approval_remarks }}</p>
                    </div>
                @endif
            @endif
        </x-app-card>

        <x-app-card class="p-6" :hover="false" :accent="$statusTone">
            <p class="text-eyebrow text-tertiary">{{ __('asnaf.show.status_eyebrow') }}</p>
            <h2 class="text-h3 text-text mt-1">{{ __('asnaf.show.status_title') }}</h2>
            <div class="mt-4 flex items-center gap-3">
                <x-status-badge :status="$status" size="md" />
            </div>
            <p class="text-small text-secondary mt-3">{{ __('asnaf.show.status_updated', ['date' => $profil->updated_at->locale(app()->getLocale())->translatedFormat('d M Y')]) }}</p>
        </x-app-card>

        <x-app-card class="p-6" :hover="false" elevated>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="text-eyebrow text-primary">{{ __('asnaf.show.ai_eyebrow') }}</p>
                    <h2 class="text-h3 text-text mt-1">{{ __('asnaf.show.ai_title') }}</h2>
                </div>
                <div class="w-11 h-11 rounded-[12px] bg-primary-soft text-primary flex items-center justify-center">
                    <i class="fa-solid fa-brain text-[16px]"></i>
                </div>
            </div>
            <div class="flex flex-col items-center text-center">
                <x-gauge-ring :percent="$aiScore" :size="140" :stroke="12" :color="$statusTone" sublabel="{{ __('asnaf.show.ai_confidence') }}" />
                <p class="text-small text-secondary mt-4">{{ $recommendation }}</p>
                <x-app-button variant="outline" size="sm" icon="fa-rotate" class="mt-4" :block="true">{{ __('asnaf.show.regenerate') }}</x-app-button>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="{{ __('asnaf.show.section_actions') }}" />
            <div class="space-y-2">
                @if ($profil->isPendingApproval())
                    <form method="POST" action="{{ route('viewprofile.approve', $profil->ic) }}">
                        @csrf
                        <x-app-button type="submit" variant="success" size="md" block icon="fa-check">{{ __('asnaf.show.approve') }}</x-app-button>
                    </form>
                    <x-app-button type="button" variant="destructive" size="md" block icon="fa-xmark" onclick="document.getElementById('rejectModal').classList.remove('hidden')">{{ __('asnaf.show.reject') }}</x-app-button>
                @endif
                @if ($profil->letter_path)
                    <a href="{{ route('viewprofile.letter', $profil->ic) }}" class="btn btn-primary btn-md w-full inline-flex items-center justify-center gap-1.5"><i class="fa-solid fa-file-pdf text-[14px]"></i> {{ __('asnaf.show.download_letter') }}</a>
                @endif
                <x-app-button variant="secondary" size="md" block icon="fa-print" onclick="window.print()">{{ __('asnaf.show.print_application') }}</x-app-button>
                <x-app-button variant="ghost" size="md" block icon="fa-arrow-left" href="{{ route('viewprofile.index') }}">{{ __('asnaf.show.back_to_list') }}</x-app-button>
            </div>
        </x-app-card>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog" aria-label="{{ __('asnaf.show.reject_modal_title') }}">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('rejectModal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-md surface p-6 rounded-[10px] shadow-lg z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-h3 text-text">{{ __('asnaf.show.reject_modal_title') }}</h3>
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="btn btn-ghost btn-icon btn-sm text-tertiary hover:text-text" aria-label="{{ __('common.actions.close') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p class="text-small text-secondary mb-4">{{ __('asnaf.show.reject_modal_intro') }} <strong>{{ $profil->nama }}</strong>{{ __('asnaf.show.reject_modal_outro') }}</p>
            <form method="POST" action="{{ route('viewprofile.reject', $profil->ic) }}">
                @csrf
                <textarea name="approval_remarks" rows="3" class="input-base w-full mb-4" placeholder="{{ __('asnaf.show.reject_reason_placeholder') }}"></textarea>
                <div class="flex items-center justify-end gap-3">
                    <x-app-button type="button" variant="ghost" size="md" onclick="document.getElementById('rejectModal').classList.add('hidden')">{{ __('asnaf.show.cancel') }}</x-app-button>
                    <x-app-button type="submit" variant="destructive" size="md" icon="fa-xmark">{{ __('asnaf.show.modal_reject') }}</x-app-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
