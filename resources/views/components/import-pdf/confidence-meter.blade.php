<div class="flex items-center gap-2">
    @php
        $color = match(true) {
            $confidence >= 90 => 'success',
            $confidence >= 75 => 'warning',
            $confidence >= 60 => 'danger',
            $confidence > 0  => 'danger',
            default => 'danger',
        };
        $label = match(true) {
            $confidence >= 90 => __('imports.preview.meter_high'),
            $confidence >= 75 => __('imports.preview.meter_medium'),
            $confidence >= 60 => __('imports.preview.meter_low'),
            $confidence > 0  => __('imports.preview.meter_review'),
            default => __('imports.preview.meter_none'),
        };
    @endphp
    <span class="text-eyebrow text-tertiary shrink-0 w-12 tabular-nums">{{ $confidence }}%</span>
    <div class="flex-1 h-1.5 rounded-pill bg-muted overflow-hidden">
        <div class="h-full rounded-pill transition-all duration-500 @switch($color)
            @case('success') bg-success @break
            @case('warning') bg-warning @break
            @case('danger')  bg-danger  @break
            @default         bg-muted
        @endswitch" style="width: {{ max(2, $confidence) }}%"></div>
    </div>
    <x-status-badge :status="$color" size="sm" :dot="false">{{ $label }}</x-status-badge>
</div>
