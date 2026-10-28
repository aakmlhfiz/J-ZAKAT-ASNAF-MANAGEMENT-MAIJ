@extends('portal.layouts.app')
@section('title', __('portal_household.household.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_household.household.title'))

@section('content')
<x-page-header
    title="{{ __('portal_household.household.title') }}"
    subtitle="{{ __('portal_household.household.subtitle') }}"
/>

@if(!$household)
    <x-app-card>
        <x-empty-state
            icon="fa-house-chimney-user"
            title="{{ __('portal_household.household.empty.title') }}"
            description="{{ __('portal_household.household.empty.description') }}"
        >
            <x-slot:action>
                <x-app-button variant="primary" size="md" icon="fa-file-circle-plus" :href="route('user.applications.create')">
                    {{ __('portal_household.household.empty.action') }}
                </x-app-button>
            </x-slot:action>
        </x-empty-state>
    </x-app-card>
@else
    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger">
        <x-stat-card :label="__('portal_household.household.summary.members')" :value="$summary['total_members']" icon="fa-users" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
        <x-stat-card :label="__('portal_household.household.summary.dependants')" :value="$summary['total_tanggungan']" icon="fa-people-roof" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
        <x-stat-card :label="__('portal_household.household.summary.income')" :value="'RM '.number_format($summary['total_income'], 2)" icon="fa-money-bill-wave" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
        <x-stat-card :label="__('portal_household.household.summary.commitments')" :value="'RM '.number_format($summary['total_expenses'], 2)" icon="fa-receipt" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
    </div>

    {{-- Ketua Keluarga --}}
    @if($ketua)
    <x-app-card class="p-6 mb-6" :hover="false">
        <x-section-header :title="__('portal_household.household.head.title')" :description="__('portal_household.household.head.description')" icon="fa-user-check" />
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><p class="text-eyebrow text-tertiary">{{ __('portal_household.household.labels.name') }}</p><p class="text-body text-text font-semibold mt-1">{{ $ketua->nama }}</p></div>
            <div><p class="text-eyebrow text-tertiary">{{ __('portal_household.household.labels.ic') }}</p><p class="text-body text-text mt-1 tabular-nums">{{ $ketua->ic ?: '—' }}</p></div>
            <div><p class="text-eyebrow text-tertiary">{{ __('portal_household.household.labels.income') }}</p><p class="text-body text-text mt-1">RM {{ number_format((float) $ketua->pendapatan_bulanan, 2) }}</p></div>
        </div>
    </x-app-card>
    @endif

    {{-- Ahli Isi Rumah --}}
    <x-app-card class="p-0" :hover="false">
        <div class="px-6 pt-5 pb-3 border-b border-divider">
            <x-section-header :title="__('portal_household.household.members.title')" :description="__('portal_household.household.members.description', ['count' => $summary['total_members']])" icon="fa-users" />
        </div>
        <div class="divide-y divide-divider">
            @forelse($members as $member)
                @php
                    $assistanceCount = ($assistanceByMember[$member->id] ?? collect())->count();
                    $tanggungan = App\Services\PortalHouseholdService::isTanggungan($member);
                    $pelajar = App\Services\PortalHouseholdService::isStudent($member);
                @endphp
                <div class="px-6 py-5">
                    <div class="flex items-start gap-4 flex-wrap">
                        <div class="w-11 h-11 rounded-full bg-primary-soft text-primary flex items-center justify-center font-semibold shrink-0">
                            {{ strtoupper(mb_substr($member->nama, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-body text-text font-semibold">{{ $member->nama }}</p>
                                <x-status-badge :status="$member->hubungan === 'Ketua Isi Rumah' ? 'primary' : 'secondary'">{{ $member->hubungan }}</x-status-badge>
                                @if($tanggungan)<x-status-badge status="warning">{{ __('portal_household.household.members.dependant') }}</x-status-badge>@endif
                                @if($pelajar)<x-status-badge status="info">{{ __('portal_household.household.members.student') }}</x-status-badge>@endif
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-5 gap-y-2 mt-3 text-small">
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.ic') }}:</span> <span class="text-text tabular-nums">{{ $member->ic ?: '—' }}</span></div>
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.age') }}:</span> <span class="text-text">{{ $member->umur ? __('portal_household.household.labels.years', ['age' => $member->umur]) : '—' }}</span></div>
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.occupation') }}:</span> <span class="text-text">{{ $member->pekerjaan ?: '—' }}</span></div>
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.income') }}:</span> <span class="text-text">RM {{ number_format((float) $member->pendapatan_bulanan, 2) }}</span></div>
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.health') }}:</span> <span class="text-text">{{ $member->status_kesihatan ?: '—' }}</span></div>
                                <div><span class="text-tertiary">{{ __('portal_household.household.labels.active_assistance') }}:</span> <span class="text-text">{{ $assistanceCount }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12">
                    <x-empty-state icon="fa-users-slash" title="{{ __('portal_household.household.members.empty_title') }}" description="{{ __('portal_household.household.members.empty_description') }}" />
                </div>
            @endforelse
        </div>
    </x-app-card>
@endif
@endsection
