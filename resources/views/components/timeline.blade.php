@props(['items' => []])
<div class="relative pl-6">
    <span class="absolute left-2 top-2 bottom-2 w-px bg-divider"></span>
    <ul class="space-y-5">
        @foreach($items as $item)
            <li class="relative">
                <span class="absolute -left-[18px] top-1.5 w-3 h-3 rounded-full {{ $item['dotClass'] ?? 'bg-primary' }} ring-4 ring-card"></span>
                <p class="text-body text-text font-medium leading-snug">{{ $item['title'] }}</p>
                @if(!empty($item['description']))<p class="text-small text-secondary mt-1">{{ $item['description'] }}</p>@endif
                @if(!empty($item['time']))<p class="text-eyebrow text-tertiary mt-1.5">{{ $item['time'] }}</p>@endif
            </li>
        @endforeach
    </ul>
</div>
