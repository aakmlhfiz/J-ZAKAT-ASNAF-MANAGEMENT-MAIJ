@props(['label' => '', 'value' => '', 'subtext' => null, 'status' => null, 'icon' => null, 'accent' => null])
@php
    $statusMap = [
        'success' => ['bg'=>'bg-success-soft', 'text'=>'text-success', 'ring'=>'ring-success/20'],
        'warning' => ['bg'=>'bg-warning-soft', 'text'=>'text-warning', 'ring'=>'ring-warning/20'],
        'danger'  => ['bg'=>'bg-danger-soft',  'text'=>'text-danger',  'ring'=>'ring-danger/20'],
        'primary' => ['bg'=>'bg-primary-soft', 'text'=>'text-primary', 'ring'=>'ring-primary/20'],
        'info'    => ['bg'=>'bg-info-soft',    'text'=>'text-info',    'ring'=>'ring-info/20'],
    ];
    $s = $statusMap[$status] ?? null;
    $hasSlot = trim((string) $slot) !== '';
@endphp
<x-app-card :accent="$accent" hover class="relative">
    @if($label)
        <p class="text-eyebrow text-tertiary">{{ $label }}</p>
    @endif
    <div class="flex items-start gap-3.5 mt-3">
        @if($icon)
            <div class="w-12 h-12 rounded-[14px] {{ $s['bg'] ?? 'bg-muted' }} {{ $s['text'] ?? 'text-text' }} flex items-center justify-center shrink-0 ring-1 {{ $s['ring'] ?? 'ring-divider' }}">
                <i class="fa-solid {{ $icon }} text-[18px]"></i>
            </div>
        @endif
        <div class="min-w-0 flex-1">
            @if($hasSlot)
                {{ $slot }}
            @elseif($value !== '')
                <p class="text-metric text-text leading-none tabular-nums">{{ $value }}</p>
            @endif
        </div>
    </div>
    @if($subtext) <p class="text-small text-secondary mt-3">{{ $subtext }}</p> @endif
    @isset($extra) <div class="mt-4 pt-4 border-t border-divider">{{ $extra }}</div> @endisset
</x-app-card>
