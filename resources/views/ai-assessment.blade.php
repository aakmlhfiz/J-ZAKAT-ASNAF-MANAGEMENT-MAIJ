@extends('layouts.app', [
    'title' => __('ai.assessment.title'),
    'subtitle' => __('ai.assessment.subtitle'),
])
@section('content')
@php
    $avgConfidence = $avgConfidence ?? 0;
    $totals = $totals ?? [];
@endphp

<x-page-header :title="__('ai.assessment.title')" :subtitle="__('ai.assessment.subtitle')" :eyebrow="__('ai.assessment.eyebrow')" icon="fa-brain">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="clipboard-check" href="{{ route('ai.page') }}">{{ __('ai.assessment.open') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 stagger">
    <x-stat-card :label="__('ai.assessment.total')" :value="number_format($items->count())" icon="clipboard-check" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    <x-stat-card :label="__('common.status.layak')" :value="number_format($totals['layak'] ?? 0)" icon="fa-circle-check" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    <x-stat-card :label="__('common.status.tidak_layak')" :value="number_format($totals['tidak_layak'] ?? 0)" icon="fa-circle-xmark" icon-bg="bg-danger-soft" icon-color="text-danger" accent="danger" />
    <x-stat-card :label="__('ai.assessment.avg_confidence')" :value="$avgConfidence . '%'" icon="fa-bullseye" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
</div>

<x-app-card class="p-6 mt-6" :hover="false">
    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h2 class="text-h3 text-text">{{ __('ai.assessment.list_title') }}</h2>
            <p class="text-small text-secondary mt-1">{{ __('ai.assessment.profiles', ['count' => $items->count()]) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 stagger">
        @forelse($items as $p)
            @php
                $color = match($p->status_kelayakan) {
                    'Layak' => 'success',
                    'Tidak Layak' => 'danger',
                    default => 'warning',
                };
                $confidence = (float) $p->ai_score;
            @endphp
            <x-app-card padding="p-5" hover>
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-11 h-11 rounded-[8px] bg-primary text-white flex items-center justify-center text-[16px] font-semibold shrink-0">
                        {{ strtoupper(mb_substr($p->nama, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('viewprofile', $p->ic) }}" class="text-body text-text font-semibold hover:text-primary transition-fast block truncate">{{ $p->nama }}</a>
                        <p class="text-small text-secondary truncate tabular-nums">{{ $p->ic }}</p>
                    </div>
                    @php
                        $statusKey = match ($p->status_kelayakan) {
                            'Layak' => 'layak',
                            'Tidak Layak' => 'tidak_layak',
                            default => 'dalam_semakan',
                        };
                    @endphp
                    <x-status-badge :status="$color">{{ __('common.status.'.$statusKey) }}</x-status-badge>
                </div>

                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="shrink-0">
                        <p class="text-eyebrow text-tertiary">{{ __('ai.assessment.score') }}</p>
                        <p class="text-metric-sm text-text tabular-nums">{{ number_format($confidence, 0) }}<span class="text-secondary text-body">%</span></p>
                    </div>
                    <div class="flex-1 h-2 bg-muted rounded-pill overflow-hidden">
                        <div class="h-full rounded-pill transition-all duration-700
                            @switch($color)
                                @case('success') bg-success @break
                                @case('danger')  bg-danger  @break
                                @default         bg-warning
                            @endswitch" style="width: {{ min(100, $confidence) }}%;"></div>
                    </div>
                </div>

                <p class="text-small text-secondary leading-relaxed line-clamp-3">{{ $p->summary ?? __('ai.assessment.not_generated') }}</p>

                <div class="mt-4 pt-4 border-t border-divider flex items-center gap-2">
                    <form method="POST" action="{{ route('ai-assessment.run') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="ic" value="{{ $p->ic }}">
                        <x-app-button type="submit" variant="secondary" size="sm" block icon="fa-rotate">{{ __('ai.assessment.generate') }}</x-app-button>
                    </form>
                    <x-app-button variant="ghost" size="sm" icon="fa-arrow-right" iconRight="" href="{{ route('viewprofile', $p->ic) }}">{{ __('ai.assessment.details') }}</x-app-button>
                </div>
            </x-app-card>
        @empty
            <div class="col-span-full">
                <x-empty-state icon="fa-brain" :title="__('ai.assessment.empty_title')" :description="__('ai.assessment.empty_desc')">
                    <x-slot:action>
                        <x-app-button variant="primary" size="md" icon="user-plus" href="{{ route('viewprofile.index') }}">{{ __('ai.assessment.select_applicant') }}</x-app-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>
</x-app-card>
@endsection
