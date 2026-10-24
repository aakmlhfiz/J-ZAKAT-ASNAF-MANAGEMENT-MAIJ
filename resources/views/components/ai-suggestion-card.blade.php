@props(['icon' => 'fa-lightbulb', 'title' => '', 'description' => null])
<button type="button" {{ $attributes->merge(['class' => 'w-full text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary hover:shadow-sm transition-apple flex items-center gap-3']) }}>
    <span class="w-8 h-8 rounded-[10px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
        <i class="fa-solid {{ $icon }} text-[12px]"></i>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-small font-medium text-text">{{ $title }}</span>
        @if($description)<span class="block text-[11px] text-secondary mt-0.5">{{ $description }}</span>@endif
    </span>
</button>
