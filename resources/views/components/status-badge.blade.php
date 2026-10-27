@props(['status' => 'default', 'dot' => true, 'size' => 'md', 'pill' => true])
@php
    $map = [
        'layak'         => ['bg'=>'bg-success-soft',  'text'=>'text-success',  'dot'=>'bg-success'],
        'tidak-layak'   => ['bg'=>'bg-danger-soft',   'text'=>'text-danger',   'dot'=>'bg-danger'],
        'dalam-semakan' => ['bg'=>'bg-warning-soft',  'text'=>'text-warning',  'dot'=>'bg-warning'],
        'sah'           => ['bg'=>'bg-success-soft',  'text'=>'text-success',  'dot'=>'bg-success'],
        'bertindih'     => ['bg'=>'bg-danger-soft',   'text'=>'text-danger',   'dot'=>'bg-danger'],
        'duplicate'     => ['bg'=>'bg-danger-soft',   'text'=>'text-danger',   'dot'=>'bg-danger'],
        'normal'        => ['bg'=>'bg-success-soft',  'text'=>'text-success',  'dot'=>'bg-success'],
        'success'       => ['bg'=>'bg-success-soft',  'text'=>'text-success',  'dot'=>'bg-success'],
        'warning'       => ['bg'=>'bg-warning-soft',  'text'=>'text-warning',  'dot'=>'bg-warning'],
        'danger'        => ['bg'=>'bg-danger-soft',   'text'=>'text-danger',   'dot'=>'bg-danger'],
        'info'          => ['bg'=>'bg-info-soft',     'text'=>'text-info',     'dot'=>'bg-info'],
        'primary'       => ['bg'=>'bg-primary-soft',  'text'=>'text-primary',  'dot'=>'bg-primary'],
    ];
    $s = $map[$status] ?? ['bg'=>'bg-muted', 'text'=>'text-secondary', 'dot'=>'bg-secondary'];
    $labels = [
        'layak' => __('common.status.layak'),
        'tidak-layak' => __('common.status.tidak_layak'),
        'dalam-semakan' => __('common.status.dalam_semakan'),
        'sah' => __('common.status.valid'),
        'bertindih' => __('common.status.overlap'),
        'duplicate' => __('common.status.duplicate'),
        'normal' => __('common.status.normal'),
        'success' => __('common.status.success'),
        'warning' => __('common.status.warning'),
        'danger' => __('common.status.danger'),
        'info' => __('common.status.info'),
        'primary' => __('common.status.primary'),
    ];
    $default = $labels[$status] ?? ucfirst(str_replace('-', ' ', $status));
    $sizeCls = $size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-[12px]';
    $radiusCls = $pill ? 'rounded-pill' : 'rounded-[6px]';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-semibold {$sizeCls} {$radiusCls} {$s['bg']} {$s['text']}"]) }}>
    @if($dot)<span class="w-1.5 h-1.5 rounded-full {{ $s['dot'] }}"></span>@endif
    {{ $slot->isEmpty() ? $default : $slot }}
</span>
