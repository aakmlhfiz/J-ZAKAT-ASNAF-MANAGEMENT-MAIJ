@props(['percent' => 85, 'size' => 120, 'stroke' => 8])
@php
    $circumference = ($size - $stroke) * M_PI;
    $offset = $circumference - ($percent / 100) * $circumference;
    $cx = $size / 2;
    $r = ($size - $stroke) / 2;
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="transform -rotate-90 shrink-0">
    <circle cx="{{ $cx }}" cy="{{ $cx }}" r="{{ $r }}"
        fill="none"
        stroke="var(--color-muted)"
        stroke-width="{{ $stroke }}" />
    <circle cx="{{ $cx }}" cy="{{ $cx }}" r="{{ $r }}"
        fill="none"
        stroke="#15345B"
        stroke-width="{{ $stroke }}"
        stroke-linecap="round"
        stroke-dasharray="{{ $circumference }}"
        stroke-dashoffset="{{ $offset }}"
        class="transition-all duration-500 ease-out" />
</svg>
