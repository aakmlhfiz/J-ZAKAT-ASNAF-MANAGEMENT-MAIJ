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
        stroke="url(#profileGradient)"
        stroke-width="{{ $stroke }}"
        stroke-linecap="round"
        stroke-dasharray="{{ $circumference }}"
        stroke-dashoffset="{{ $offset }}"
        class="transition-all duration-700 ease-out" />
    <defs>
        <linearGradient id="profileGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#2563EB" />
            <stop offset="100%" stop-color="#4F46E5" />
        </linearGradient>
    </defs>
</svg>
