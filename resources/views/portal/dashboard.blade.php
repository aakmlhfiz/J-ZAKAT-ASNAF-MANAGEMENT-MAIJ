@extends('portal.layouts.app')
@section('title', __('applications.portal.title'))
@section('page-title', __('applications.portal.page_title'))

@section('content')
@php
    $user = auth('portal')->user();
    $firstName = explode(' ', $user->name)[0];
    $hasApplication = $applications->isNotEmpty();
    $statusLabels = [
        'draft' => __('applications.status.draft'),
        'incomplete' => __('applications.status.incomplete'),
        'submitted' => __('applications.status.submitted'),
        'under_review' => __('applications.status.under_review'),
        'approved' => __('applications.status.approved'),
        'rejected' => __('applications.status.rejected'),
    ];
@endphp

{{-- Welcome --}}
<div class="mb-6">
    <h1 class="text-title text-text">{{ __('applications.portal.welcome', ['name' => $firstName]) }}</h1>
    <p class="text-body text-secondary mt-1">{{ __('applications.portal.subtitle') }}</p>
</div>

{{-- Metric Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6 stagger">
    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                <i class="fa-solid fa-file-lines text-[20px]"></i>
            </div>
            @if($stats['total'] > 0)
            <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-success bg-success-soft px-2 py-0.5 rounded-full">
                <i class="fa-solid fa-arrow-trend-up text-[9px]"></i>
                +{{ $stats['total'] * 10 }}%
            </span>
            @endif
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.total') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['total'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.total_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="{{ json_encode($trendData) }}"></canvas></div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-warning-soft text-warning flex items-center justify-center shrink-0">
                <i class="fa-solid fa-pen-to-square text-[20px]"></i>
            </div>
            <span class="text-[11px] font-semibold text-warning bg-warning-soft px-2 py-0.5 rounded-full">{{ $stats['draft'] }}</span>
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.draft') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['draft'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.draft_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="[0,0,0,0,0,0,0,0,0,0,0,0]"></canvas></div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-success-soft text-success flex items-center justify-center shrink-0">
                <i class="fa-solid fa-paper-plane text-[20px]"></i>
            </div>
            <span class="text-[11px] font-semibold text-success bg-success-soft px-2 py-0.5 rounded-full">{{ $stats['submitted'] }}</span>
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.submitted') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['submitted'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.submitted_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="[0,0,0,0,0,0,0,0,0,0,0,0]"></canvas></div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-info-soft text-info flex items-center justify-center shrink-0">
                <i class="fa-solid fa-magnifying-glass-chart text-[20px]"></i>
            </div>
            <span class="text-[11px] font-semibold text-info bg-info-soft px-2 py-0.5 rounded-full">{{ $stats['under_review'] }}</span>
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.under_review') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['under_review'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.under_review_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="[0,0,0,0,0,0,0,0,0,0,0,0]"></canvas></div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-success-soft text-success flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-[20px]"></i>
            </div>
            <span class="text-[11px] font-semibold text-success bg-success-soft px-2 py-0.5 rounded-full">{{ $stats['approved'] }}</span>
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.approved') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['approved'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.approved_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="[0,0,0,0,0,0,0,0,0,0,0,0]"></canvas></div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-[14px] bg-danger-soft text-danger flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-xmark text-[20px]"></i>
            </div>
            <span class="text-[11px] font-semibold text-danger bg-danger-soft px-2 py-0.5 rounded-full">{{ $stats['rejected'] }}</span>
        </div>
        <h3 class="text-small text-secondary">{{ __('applications.portal.stats.rejected') }}</h3>
        <p class="text-metric-lg text-text mt-1 tabular-nums leading-tight">{{ $stats['rejected'] }}</p>
        <p class="text-caption text-tertiary mt-1">{{ __('applications.portal.stats.rejected_caption') }}</p>
        <div class="mt-3 h-8"><canvas class="sparkline" data-values="[0,0,0,0,0,0,0,0,0,0,0,0]"></canvas></div>
    </x-app-card>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6 stagger">
    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-h3 text-text flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-tertiary text-[14px]"></i>
                {{ __('applications.portal.trend_title') }}
            </h3>
            <div class="flex items-center gap-2">
                @if(!$hasData)
                    <span class="text-eyebrow text-secondary bg-muted px-2 py-0.5 rounded-full">{{ __('applications.portal.sample') }}</span>
                @endif
                <select class="text-small h-7 px-2 rounded-md border border-input-border bg-card text-text focus:outline-none focus:border-primary cursor-pointer">
                    <option>{{ __('applications.portal.trend_yearly') }}</option>
                    <option>{{ __('applications.portal.trend_monthly') }}</option>
                </select>
            </div>
        </div>
        <div class="h-56">
            <canvas id="trendChart"></canvas>
        </div>
    </x-app-card>

    <x-app-card class="p-5" hover>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-h3 text-text flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-tertiary text-[14px]"></i>
                {{ __('applications.portal.status_title') }}
            </h3>
            <select class="text-small h-7 px-2 rounded-md border border-input-border bg-card text-text focus:outline-none focus:border-primary cursor-pointer">
                <option>{{ __('applications.portal.status_all') }}</option>
                <option>{{ __('applications.portal.status_this_year') }}</option>
            </select>
        </div>
        <div class="h-56 flex items-center justify-center">
            @if($hasApplication)
                <canvas id="statusChart"></canvas>
            @else
                <div class="text-center text-tertiary">
                    <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-chart-pie text-[24px] opacity-30"></i>
                    </div>
                    <p class="text-small text-secondary">{{ __('applications.portal.no_applications') }}</p>
                </div>
            @endif
        </div>
    </x-app-card>
</div>

{{-- Latest Application Status --}}
@if($latestApplication)
<x-app-card class="p-6 mb-6" :hover="false">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-h3 text-text flex items-center gap-2">
            <i class="fa-solid fa-list-check text-tertiary text-[14px]"></i>
            {{ __('applications.portal.latest_status_title') }}
        </h3>
        <a href="{{ route('user.applications.status') }}" class="text-small text-primary font-medium hover:underline">{{ __('applications.portal.view_all') }}</a>
    </div>
    <x-application-timeline :application="$latestApplication" />
</x-app-card>
@endif

{{-- Main + Sidebar --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 stagger">
    <div class="lg:col-span-2 space-y-5">

        {{-- Permohonan Terkini --}}
        <x-app-card class="p-5" hover>
            <div class="px-5 py-4 border-b border-divider flex items-center justify-between">
                <h3 class="text-h3 text-text flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-tertiary text-[14px]"></i>
                    {{ __('applications.portal.recent_title') }}
                </h3>
                @if($hasApplication)
                    <span class="text-small text-secondary">{{ __('applications.portal.recent_count') }}</span>
                @endif
            </div>
            @if($hasApplication)
                <div class="divide-y divide-divider">
                    @foreach($applications->take(5) as $app)
                        <a href="{{ route('user.applications.show', $app->id) }}" class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-muted transition-colors block">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-primary-soft text-primary flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-lines text-[14px]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-body text-text font-medium truncate">{{ $app->application_no }}</p>
                                    <p class="text-small text-secondary">{{ $app->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium shrink-0
                                {{ $app->status === 'draft' ? 'bg-warning-soft text-warning' : '' }}
                                {{ $app->status === 'submitted' ? 'bg-primary-soft text-primary' : '' }}
                                {{ $app->status === 'under_review' ? 'bg-info-soft text-info' : '' }}
                                {{ $app->status === 'approved' ? 'bg-success-soft text-success' : '' }}
                                {{ $app->status === 'rejected' ? 'bg-danger-soft text-danger' : '' }}">
                                {{ $statusLabels[$app->status] ?? ucfirst(str_replace('_', ' ', $app->status)) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-16 text-center">
                    <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mx-auto mb-5">
                        <i class="fa-solid fa-file-lines text-[32px] opacity-20"></i>
                    </div>
                    <h3 class="text-h3 text-text">{{ __('applications.portal.no_applications') }}</h3>
                    <p class="text-body text-secondary mt-1.5 max-w-[280px] mx-auto">{{ __('applications.portal.coming_soon') }}</p>
                </div>
            @endif
        </x-app-card>


    </div>

    {{-- Right Sidebar --}}
    <div class="space-y-5">

        {{-- Notifikasi --}}
        <x-app-card class="p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-h3 text-text">{{ __('applications.portal.notifications_title') }}</h3>
                @if($unreadNotifications > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-danger text-white text-[11px] font-bold">{{ $unreadNotifications }}</span>
                @endif
            </div>
            <p class="text-small text-secondary">{{ $unreadNotifications > 0 ? trans_choice('applications.portal.notifications_unread', $unreadNotifications, ['count' => $unreadNotifications]) : __('applications.portal.notifications_none') }}</p>
            <a href="{{ route('user.notifications.index') }}" class="btn btn-outline btn-sm w-full justify-center mt-3">
                <i class="fa-solid fa-bell text-[12px]"></i> {{ __('applications.portal.view_notifications') }}
            </a>
        </x-app-card>

        {{-- Tindakan Pantas --}}
        <x-app-card class="p-5">
            <h3 class="text-h3 text-text mb-4">{{ __('applications.portal.quick_title') }}</h3>
            <div class="space-y-2">
                <a href="{{ route('user.applications.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-muted text-secondary hover:bg-divider transition-apple text-body font-medium w-full text-left">
                    <div class="w-8 h-8 rounded-lg bg-divider flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-circle-plus text-[14px]"></i>
                    </div>
                    <span>{{ __('applications.portal.quick_apply') }}</span>
                </a>
                <a href="{{ route('user.applications.scan.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-muted text-secondary hover:bg-divider transition-apple text-body font-medium w-full text-left">
                    <div class="w-8 h-8 rounded-lg bg-divider flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-scanner text-[14px]"></i>
                    </div>
                    <span>{{ __('applications.portal.quick_scan') }}</span>
                </a>
                <a href="{{ route('user.hadkifayah') }}" class="flex items-center gap-3 p-3 rounded-lg bg-muted text-secondary hover:bg-divider transition-apple text-body font-medium w-full text-left">
                    <div class="w-8 h-8 rounded-lg bg-divider flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-calculator text-[14px]"></i>
                    </div>
                    <span>{{ __('applications.portal.quick_kifayah') }}</span>
                </a>
            </div>
        </x-app-card>

        {{-- Cara Mohon --}}
        <x-app-card class="p-5" hover>
            <div class="p-5 text-white" style="background: var(--gradient-brand);">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-circle-question text-white text-[18px]"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-semibold">{{ __('applications.portal.howto_title') }}</h3>
                        <p class="text-[12px] text-white/70">{{ __('applications.portal.howto_subtitle') }}</p>
                    </div>
                </div>
                <p class="text-small text-white/80 leading-relaxed">{{ __('applications.portal.howto_desc') }}</p>
            </div>
            <div class="p-4">
                <a href="{{ route('user.applications.create') }}" class="btn btn-primary btn-sm w-full justify-center">{{ __('applications.portal.apply_now') }}</a>
            </div>
        </x-app-card>

        {{-- Profil Saya --}}
        <x-app-card class="p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-full text-white font-semibold text-[16px] flex items-center justify-center shrink-0" style="background: var(--gradient-royal);">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-body text-text font-semibold truncate">{{ $user->name }}</p>
                    <p class="text-small text-secondary">{{ __('common.roles.portal') }}</p>
                </div>
            </div>
            <div class="space-y-2 text-small text-secondary">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-tertiary text-[12px] w-4 shrink-0"></i>
                    <span class="truncate">{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-phone text-tertiary text-[12px] w-4 shrink-0"></i>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
            </div>
        </x-app-card>
    </div>
</div>

{{-- Aktiviti Terkini --}}
<x-app-card class="p-5 mt-5">
    <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
        <i class="fa-solid fa-list-timeline text-tertiary text-[14px]"></i>
        {{ __('applications.portal.activity_title') }}
    </h3>
    <div class="py-8 text-center text-tertiary">
        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-clock text-[20px] opacity-30"></i>
        </div>
        <p class="text-small text-secondary">{{ __('applications.portal.activity_empty') }}</p>
    </div>
</x-app-card>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendLabels = @json($trendLabels);
        const trendData = @json($trendData);
        const statusData = @json($statusData);
        const hasData = @json($hasData);

        const trendCtx = document.getElementById('trendChart');
        if (trendCtx && window.Chart) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: @json(__('applications.portal.chart_applications')),
                        data: trendData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#3B82F6',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 11 }, color: '#94A3B8' }, grid: { color: '#F1F5F9' } },
                        x: { ticks: { font: { size: 11 }, color: '#94A3B8' }, grid: { display: false } }
                    }
                }
            });
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && window.Chart && hasData) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#F59E0B', '#3B82F6', '#38BDF8', '#22C55E', '#EF4444'],
                        borderColor: '#FFFFFF',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true, font: { size: 12 } }
                        }
                    }
                }
            });
        }

        document.querySelectorAll('.sparkline').forEach(canvas => {
            const values = JSON.parse(canvas.dataset.values || '[]');
            if (!window.Chart || !values.length) return;
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: values.map((_, i) => i),
                    datasets: [{
                        data: values,
                        borderColor: '#3B82F6',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.4,
                        pointRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { display: false }, y: { display: false } },
                }
            });
        });
    });
</script>
@endpush
