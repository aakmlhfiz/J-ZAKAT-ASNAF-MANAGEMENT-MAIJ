@php
    $bubbleCls = $msg['role'] === 'user' ? 'bg-primary text-white' : 'bg-card border border-divider';
@endphp
<div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-[80%] rounded-[20px] px-4 py-2.5 {{ $bubbleCls }}">
        <p class="text-small leading-relaxed">{{ $msg['text'] ?? '' }}</p>
        @if(!empty($msg['time']))<p class="text-[10px] opacity-70 mt-1">{{ $msg['time'] }}</p>@endif
    </div>
</div>
