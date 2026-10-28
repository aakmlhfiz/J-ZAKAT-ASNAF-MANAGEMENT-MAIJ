@props(['icon' => 'fa-circle', 'description' => '', 'detail' => null, 'time' => '', 'status' => 'success', 'ip' => null])
@php
    $statusMap = [
        'success' => ['bg' => 'bg-success-soft', 'text' => 'text-success', 'dot' => 'bg-success'],
        'warning' => ['bg' => 'bg-warning-soft', 'text' => 'text-warning', 'dot' => 'bg-warning'],
        'danger'  => ['bg' => 'bg-danger-soft', 'text' => 'text-danger', 'dot' => 'bg-danger'],
        'info'    => ['bg' => 'bg-info-soft', 'text' => 'text-info', 'dot' => 'bg-info'],
        'primary' => ['bg' => 'bg-primary-soft', 'text' => 'text-primary', 'dot' => 'bg-primary'],
    ];
    $s = $statusMap[$status] ?? $statusMap['primary'];
    $statusLabel = match($status) {
        'success' => __('profile.activity.status_success'),
        'warning' => __('profile.activity.status_warning'),
        'danger' => __('profile.activity.status_danger'),
        'info' => __('profile.activity.status_info'),
        'primary' => __('profile.activity.status_primary'),
        default => ucfirst($status),
    };
    $actIcon = match($icon) {
        'login' => 'fa-right-to-bracket',
        'logout' => 'fa-right-from-bracket',
        'profile_update' => 'fa-pen-to-square',
        'password_change' => 'fa-key',
        'photo_upload' => 'fa-camera',
        'settings_update' => 'fa-sliders',
        '2fa_enable' => 'fa-shield-halved',
        '2fa_disable' => 'fa-shield',
        default => 'fa-circle',
    };
@endphp
<li class="relative pl-8 pb-6 last:pb-0">
    <span class="absolute left-0 top-0 bottom-0 w-px bg-divider"></span>
    <span class="absolute left-[-5px] top-1 w-2.5 h-2.5 rounded-full {{ $s['dot'] }} ring-4 ring-card z-10"></span>
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-[10px] {{ $s['bg'] }} {{ $s['text'] }} flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid {{ $actIcon }} text-[13px]"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-body text-text font-medium leading-snug">{{ $description }}</p>
            @if($detail)<p class="text-small text-secondary mt-0.5">{{ $detail }}</p>@endif
            @if($ip)<p class="text-eyebrow text-tertiary mt-0.5">IP: {{ $ip }}</p>@endif
        </div>
        <div class="text-right shrink-0">
            <p class="text-small text-tertiary whitespace-nowrap">{{ $time }}</p>
            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-pill text-[11px] font-semibold {{ $s['bg'] }} {{ $s['text'] }}">
                {{ $statusLabel }}
            </span>
        </div>
    </div>
</li>
