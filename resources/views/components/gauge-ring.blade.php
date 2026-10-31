@props(['percent' => 0, 'size' => 120, 'stroke' => 10, 'color' => 'primary', 'label' => null, 'sublabel' => null])
@php
    $clamped = max(0, min(100, (float) $percent));
    $colorMap = [
        'primary' => '#15345B',
        'success' => '#15803D',
        'warning' => '#D97706',
        'danger'  => '#DC2626',
        'info'    => '#245B91',
    ];
    $strokeColor = $colorMap[$color] ?? $colorMap['primary'];
    $radius = ($size - $stroke) / 2;
    $circ = 2 * M_PI * $radius;
    $offset = $circ * (1 - $clamped / 100);
    $uid = 'gauge-' . uniqid();
@endphp
<div class="inline-flex flex-col items-center">
    <div class="relative" style="width: {{ $size }}px; height: {{ $size }}px;">
        <svg viewBox="0 0 {{ $size }} {{ $size }}" class="transform -rotate-90">
            <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" stroke="#E2E8F0" stroke-width="{{ $stroke }}" fill="none" />
            <circle data-gauge cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}" stroke="{{ $strokeColor }}" stroke-width="{{ $stroke }}" fill="none"
                stroke-linecap="round"
                stroke-dasharray="{{ $circ }}"
                stroke-dashoffset="{{ $circ }}"
                data-target="{{ $offset }}"
                style="transition: stroke-dashoffset 1s cubic-bezier(0.16, 1, 0.3, 1);" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <p class="text-metric text-text tabular-nums" style="font-size: {{ max(20, $size * 0.28) }}px;">{{ round($clamped) }}<span class="text-secondary" style="font-size: {{ max(11, $size * 0.16) }}px;">%</span></p>
            @if($label)<p class="text-eyebrow text-tertiary mt-0.5">{{ $label }}</p>@endif
        </div>
    </div>
    @if($sublabel)<p class="text-small text-secondary mt-2">{{ $sublabel }}</p>@endif
</div>

@once
@push('scripts')
<script type="module">
    const animateGauges = () => {
        document.querySelectorAll('svg circle[data-gauge]').forEach((c) => {
            if (c.dataset.animated === '1') return;
            c.dataset.animated = '1';
            const target = parseFloat(c.dataset.target || '0');
            requestAnimationFrame(() => { c.style.strokeDashoffset = String(target); });
        });
    };
    if (document.readyState !== 'loading') animateGauges();
    else document.addEventListener('DOMContentLoaded', animateGauges);
    new MutationObserver(() => animateGauges()).observe(document.body, { childList: true, subtree: true });
</script>
@endpush
@endonce
