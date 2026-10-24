@props([
    'padding' => 'p-5 md:p-6',
    'hover' => false,
    'elevated' => false,
    'flat' => false,
    'as' => 'div',
    'accent' => null,
])
@php
    $base = $flat ? 'surface-flat' : ($elevated ? 'surface-elevated' : 'surface');
    $classes = collect([
        $base,
        $padding,
        $hover ? 'surface-hover' : '',
        $accent ? 'accent-bar accent-' . $accent : '',
    ])->filter()->implode(' ');
@endphp
<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $as }}>
