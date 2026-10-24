@props(['device' => 'Desktop', 'browser' => 'Chrome', 'os' => 'Windows', 'location' => 'Johor Bahru, MY', 'current' => false, 'time' => ''])
<div class="flex items-center gap-4 py-3.5 border-b border-divider last:border-b-0">
    <div class="w-10 h-10 rounded-[12px] {{ $current ? 'bg-primary-soft text-primary' : 'bg-muted text-tertiary' }} flex items-center justify-center shrink-0">
        <i class="fa-solid {{ $current ? 'fa-desktop' : 'fa-mobile-screen' }} text-[15px]"></i>
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <p class="text-body text-text font-medium">{{ $device }}</p>
            @if($current)
                <span class="px-2 py-0.5 rounded-pill text-[10px] font-semibold bg-success-soft text-success">Semasa</span>
            @endif
        </div>
        <p class="text-small text-secondary mt-0.5">{{ $browser }} · {{ $os }} · {{ $location }}</p>
    </div>
    <div class="text-right shrink-0">
        <p class="text-small text-tertiary whitespace-nowrap">{{ $time }}</p>
    </div>
</div>
