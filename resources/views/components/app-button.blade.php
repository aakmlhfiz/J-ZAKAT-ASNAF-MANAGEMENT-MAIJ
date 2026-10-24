@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'iconOnly' => false,
    'block' => false,
])
@php
    $tag = $href ? 'a' : 'button';
    $sizeCls = $iconOnly ? "btn-icon btn-{$size}" : "btn-{$size}";
    $classes = collect([
        'btn',
        "btn-{$variant}",
        $sizeCls,
        $block ? 'w-full' : '',
    ])->filter()->implode(' ');
@endphp
<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($icon)<x-icon :name="$icon" :size="$size === 'lg' ? 18 : 16" />@endif
    @if(!$iconOnly)<span>{{ $slot }}</span>@endif
    @if($iconRight && !$iconOnly)<x-icon :name="$iconRight" :size="16" />@endif
</{{ $tag }}>
