@props([
    'label' => '', 'value' => 0, 'trend' => null, 'trendUp' => true, 'trendLabel' => null,
    'icon' => null, 'iconBg' => 'bg-primary-soft', 'iconColor' => 'text-primary',
    'href' => null, 'accent' => 'primary',
    'sparkline' => null,
    'xValue' => null,
])
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'surface surface-hover p-5 block group relative overflow-hidden';
    $accentMap = [
        'primary' => '#15345B',
        'success' => '#15803D',
        'warning' => '#D97706',
        'danger'  => '#DC2626',
        'info'    => '#245B91',
    ];
    $accentBg = $accentMap[$accent] ?? $accentMap['primary'];
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    <div class="absolute top-0 left-0 right-0 h-0.5" style="background: {{ $accentBg }};"></div>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="text-eyebrow text-tertiary">{{ $label }}</p>
            @if($xValue)
                <p class="text-metric-sm text-text mt-2 truncate tabular-nums" x-text="{{ $xValue }}"></p>
            @else
                <p class="text-metric-sm text-text mt-2 truncate tabular-nums">{{ $value }}</p>
            @endif
            @if($trend || $trendLabel)
                <div class="mt-2.5 flex items-center gap-1.5 text-small">
                    @if($trend)
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-[6px] {{ $trendUp ? 'text-success bg-success-soft' : 'text-danger bg-danger-soft' }}">
                            <x-icon :name="$trendUp ? 'trending-up' : 'trending-down'" :size="12" />
                            {{ $trend }}
                        </span>
                    @endif
                    @if($trendLabel)<span class="text-tertiary">{{ $trendLabel }}</span>@endif
                </div>
            @endif
        </div>
        @if($icon)
            <div class="icon-chip w-10 h-10 {{ $iconBg }} {{ $iconColor }} flex items-center justify-center shrink-0">
                <x-icon :name="$icon" :size="21" />
            </div>
        @endif
    </div>
    @if($sparkline && is_array($sparkline) && count($sparkline) > 1)
        <div class="mt-4 -mb-1">
            <x-sparkline :data="$sparkline" :color="match($accent) { 'success' => '#15803D', 'warning' => '#D97706', 'danger' => '#DC2626', 'info' => '#245B91', default => '#15345B' }" :height="32" />
        </div>
    @endif
</{{ $tag }}>
