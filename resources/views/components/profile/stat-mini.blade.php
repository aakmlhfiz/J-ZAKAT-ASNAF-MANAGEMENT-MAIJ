@props(['label' => '', 'value' => '', 'icon' => 'fa-circle', 'accent' => 'primary'])
@php
    $accentMap = ['primary' => 'bg-primary', 'success' => 'bg-success', 'warning' => 'bg-warning', 'danger' => 'bg-danger', 'info' => 'bg-info'];
    $bar = $accentMap[$accent] ?? 'bg-primary';
@endphp
<div class="surface p-4 relative overflow-hidden">
    <div class="absolute top-0 left-0 right-0 h-1 {{ $bar }} opacity-80 rounded-t-[20px]"></div>
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-[10px] bg-primary-soft text-primary flex items-center justify-center shrink-0">
            <i class="fa-solid {{ $icon }} text-[13px]"></i>
        </div>
        <div class="min-w-0">
            <p class="text-eyebrow text-tertiary">{{ $label }}</p>
            <p class="text-body text-text font-semibold mt-0.5 truncate">{{ $value }}</p>
        </div>
    </div>
</div>
