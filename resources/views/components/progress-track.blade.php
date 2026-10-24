@props(['label' => null, 'current' => 0, 'total' => 4, 'percent' => null])
@php
    $pct = $percent ?? ($total > 0 ? ($current / $total) * 100 : 0);
@endphp
<div class="flex items-center gap-3 text-small">
    @if($label)<span class="text-secondary font-medium">{{ $label }}</span>@endif
    <div class="flex-1 h-1.5 rounded-pill bg-muted overflow-hidden">
        <div class="h-full rounded-pill bg-primary transition-all duration-500 ease-out" style="width: {{ $pct }}%;"></div>
    </div>
    <span class="text-text font-semibold tabular-nums">{{ $current }}<span class="text-tertiary">/{{ $total }}</span></span>
</div>
