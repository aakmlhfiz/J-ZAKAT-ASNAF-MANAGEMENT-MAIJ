@props(['title' => '', 'subtitle' => null, 'eyebrow' => null, 'icon' => null])
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-7">
    <div class="min-w-0 flex-1">
        @if($eyebrow)
            <p class="text-eyebrow text-primary mb-2 flex items-center gap-2">
                @if($icon)<x-icon :name="$icon" :size="14" />@endif
                {{ $eyebrow }}
            </p>
        @endif
        <h1 class="text-title text-text tracking-tight">{{ $title }}</h1>
        @if($subtitle)<p class="text-body text-secondary-strong mt-2 max-w-2xl">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2 shrink-0 flex-wrap">{{ $actions }}</div>
    @endisset
</div>
