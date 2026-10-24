@props(['steps' => [], 'current' => 0])
<ol class="flex items-center w-full gap-2 overflow-x-auto pb-2">
    @foreach($steps as $i => $step)
        @php
            $state = $i < $current ? 'done' : ($i === $current ? 'current' : 'upcoming');
            $circleCls = $state === 'done' ? 'bg-primary text-white' : ($state === 'current' ? 'bg-primary text-white ring-4 ring-primary/15' : 'bg-muted text-tertiary');
            $textCls   = $state === 'upcoming' ? 'text-tertiary' : 'text-text font-semibold';
        @endphp
        <li class="flex items-center gap-3 shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-[12px] font-semibold transition-apple {{ $circleCls }}">
                    @if($state === 'done')<i class="fa-solid fa-check text-[10px]"></i>@else{{ $i + 1 }}@endif
                </span>
                <span class="text-small {{ $textCls }} whitespace-nowrap">{{ $step }}</span>
            </div>
            @if(!$loop->last)
                <span class="hidden sm:block w-8 h-px {{ $state === 'done' ? 'bg-primary' : 'bg-divider' }}"></span>
            @endif
        </li>
    @endforeach
</ol>
