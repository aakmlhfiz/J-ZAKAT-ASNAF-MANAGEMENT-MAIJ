@props(['eyebrow' => null, 'title' => '', 'subtitle' => null, 'icon' => null, 'accent' => 'primary'])
<x-app-card :accent="$accent" class="relative overflow-hidden">
    <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full opacity-50 pointer-events-none" style="background: radial-gradient(circle, rgba(0,102,204,0.10) 0%, transparent 70%);"></div>
    <div class="relative flex items-start gap-5">
        @if($icon)
            <div class="w-14 h-14 rounded-[16px] bg-primary-soft text-primary flex items-center justify-center shrink-0 ring-1 ring-primary/10">
                <i class="fa-solid {{ $icon }} text-[20px]"></i>
            </div>
        @endif
        <div class="min-w-0 flex-1">
            @if($eyebrow)<p class="text-eyebrow text-primary mb-1.5">{{ $eyebrow }}</p>@endif
            <h1 class="text-display text-text tracking-tight">{{ $title }}</h1>
            @if($subtitle)<p class="text-body text-secondary mt-2 max-w-2xl">{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)<div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>@endisset
    </div>
    @isset($footer)<div class="relative mt-5 pt-5 border-t border-divider">{{ $footer }}</div>@endisset
</x-app-card>
