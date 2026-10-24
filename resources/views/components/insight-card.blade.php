@props(['tone' => 'primary', 'icon' => null, 'title' => '', 'description' => null])
@php
    $toneMap = [
        'primary' => ['bg' => 'bg-primary-soft', 'text' => 'text-primary', 'ring' => 'ring-primary/15'],
        'success' => ['bg' => 'bg-success-soft', 'text' => 'text-success', 'ring' => 'ring-success/15'],
        'warning' => ['bg' => 'bg-warning-soft', 'text' => 'text-warning', 'ring' => 'ring-warning/15'],
        'danger'  => ['bg' => 'bg-danger-soft',  'text' => 'text-danger',  'ring' => 'ring-danger/15'],
        'info'    => ['bg' => 'bg-info-soft',    'text' => 'text-info',    'ring' => 'ring-info/15'],
    ];
    $t = $toneMap[$tone] ?? $toneMap['primary'];
@endphp
<div class="flex items-start gap-4 p-4 rounded-[14px] {{ $t['bg'] }} ring-1 {{ $t['ring'] }} transition-apple hover:translate-x-0.5">
    @if($icon)
        <div class="w-10 h-10 rounded-[10px] bg-card {{ $t['text'] }} flex items-center justify-center shrink-0 shadow-xs">
            <i class="fa-solid {{ $icon }} text-[14px]"></i>
        </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-body text-text font-semibold leading-snug">{{ $title }}</p>
        @if($description)<p class="text-small text-secondary mt-1 leading-relaxed">{{ $description }}</p>@endif
    </div>
</div>
