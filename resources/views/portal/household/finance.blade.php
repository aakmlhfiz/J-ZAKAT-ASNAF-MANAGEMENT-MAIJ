@extends('portal.layouts.app')
@section('title', __('portal_household.finance.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_household.finance.title'))

@section('content')
<x-page-header
    title="{{ __('portal_household.finance.title') }}"
    subtitle="{{ __('portal_household.finance.subtitle') }}"
/>

@if(!$household)
    <x-app-card>
        <x-empty-state icon="fa-wallet" title="{{ __('portal_household.finance.empty.title') }}" description="{{ __('portal_household.finance.empty.description') }}" />
    </x-app-card>
@else
    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 stagger">
        <x-stat-card :label="__('portal_household.finance.summary.income')" :value="'RM '.number_format($summary['total_income'], 2)" icon="fa-money-bill-trend-up" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
        <x-stat-card :label="__('portal_household.finance.summary.commitments')" :value="'RM '.number_format($summary['total_expenses'], 2)" icon="fa-file-invoice-dollar" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
        <x-stat-card :label="__('portal_household.finance.summary.dependants')" :value="$summary['total_tanggungan']" icon="fa-people-roof" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Income --}}
        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('portal_household.finance.income.title')" :description="__('portal_household.finance.income.description')" icon="fa-money-bill-wave" />
            <dl class="space-y-3 text-body">
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('portal_household.finance.income.applicant') }}</span><span class="text-text font-semibold">RM {{ number_format($income['applicant'], 2) }}</span></div>
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('portal_household.finance.income.spouse') }}</span><span class="text-text font-semibold">RM {{ number_format($income['spouse'], 2) }}</span></div>
                <div class="flex justify-between py-2 border-b border-divider"><span class="text-secondary">{{ __('portal_household.finance.income.other') }}</span><span class="text-text font-semibold">RM {{ number_format($income['other'], 2) }}</span></div>
                <div class="flex justify-between pt-2"><span class="text-text font-semibold">{{ __('portal_household.finance.income.total') }}</span><span class="text-text font-bold">RM {{ number_format($summary['total_income'], 2) }}</span></div>
            </dl>
        </x-app-card>

        {{-- Commitments --}}
        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('portal_household.finance.commitments.title')" :description="__('portal_household.finance.commitments.description')" icon="fa-receipt" />
            <div class="py-8 text-center">
                <div class="w-14 h-14 rounded-full bg-warning-soft text-warning flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-invoice-dollar text-[22px]"></i>
                </div>
                <p class="text-metric-lg text-text">RM {{ number_format($summary['total_expenses'], 2) }}</p>
                <p class="text-small text-secondary mt-1">{{ __('portal_household.finance.commitments.recorded') }}</p>
            </div>
        </x-app-card>
    </div>

    {{-- Net summary card --}}
    @php $net = $summary['total_income'] - $summary['total_expenses']; @endphp
    <x-app-card class="p-6 mt-6" :hover="false" :accent="$net >= 0 ? 'success' : 'danger'">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-eyebrow text-tertiary">{{ __('portal_household.finance.balance.label') }}</p>
                <h2 class="text-h3 text-text mt-1">RM {{ number_format($net, 2) }}</h2>
            </div>
            <x-status-badge :status="$net >= 0 ? 'success' : 'danger'">{{ $net >= 0 ? __('portal_household.finance.balance.surplus') : __('portal_household.finance.balance.deficit') }}</x-status-badge>
        </div>
    </x-app-card>
@endif
@endsection
