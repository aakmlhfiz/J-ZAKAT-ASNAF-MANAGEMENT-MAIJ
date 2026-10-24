@props(['title' => '', 'description' => null, 'icon' => null, 'accent' => 'primary'])
<x-app-card :accent="$accent" {{ $attributes->merge(['class' => 'animate-fade-in']) }}>
    @if($title || $description || $icon)
        <header class="flex items-start gap-4 mb-6">
            @if($icon)
                <div class="w-11 h-11 rounded-[12px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $icon }} text-[15px]"></i>
                </div>
            @endif
            <div class="min-w-0 flex-1">
                @if($title)<h2 class="text-h3 text-text">{{ $title }}</h2>@endif
                @if($description)<p class="text-small text-secondary mt-1">{{ $description }}</p>@endif
            </div>
            @isset($headerActions)<div class="shrink-0">{{ $headerActions }}</div>@endisset
        </header>
    @endif
    <div class="space-y-4">{{ $slot }}</div>
</x-app-card>
