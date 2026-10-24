@props(['progress' => 0, 'status' => 'processing', 'confidence' => null, 'error' => null])

@php
    $statusLabel = match($status) {
        'processing'   => 'Memproses...',
        'extracted'    => 'Sedia untuk pratinjau',
        'needs_review' => 'Perlu semakan manual',
        'committed'    => 'Data disimpan',
        'failed'       => 'Gagal',
        default        => 'Menunggu',
    };
    $statusIcon = match($status) {
        'processing'   => 'fa-spinner fa-spin-pulse',
        'extracted'    => 'fa-circle-check',
        'needs_review' => 'fa-circle-exclamation',
        'committed'    => 'fa-database',
        'failed'       => 'fa-circle-exclamation',
        default        => 'fa-clock',
    };
    $statusColor = match($status) {
        'processing'   => 'primary',
        'extracted'    => 'success',
        'needs_review' => 'warning',
        'committed'    => 'success',
        'failed'       => 'danger',
        default        => 'secondary',
    };
@endphp

<x-app-card class="p-5" :hover="false" accent="primary">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-[12px] bg-primary-soft flex items-center justify-center">
            <i class="fa-solid {{ $statusIcon }} text-primary"></i>
        </div>
        <div>
            <p class="text-body text-text font-semibold">Status Pengekstrakan</p>
            <p class="text-small text-secondary">{{ $statusLabel }}</p>
        </div>
        @if($confidence !== null)
            <div class="ml-auto text-right">
                <p class="text-eyebrow text-tertiary">Keyakinan</p>
                <p class="text-metric-sm text-text tabular-nums">{{ $confidence }}<span class="text-secondary text-body">%</span></p>
            </div>
        @endif
    </div>

    @if($error)
        <div class="soft-panel p-3 text-small text-danger flex items-start gap-2">
            <i class="fa-solid fa-circle-exclamation text-[13px] mt-0.5 shrink-0"></i>
            <span>{{ $error }}</span>
        </div>
    @endif

    @if($status === 'processing')
        <div class="h-2 bg-muted rounded-pill overflow-hidden">
            <div class="h-full bg-primary rounded-pill transition-all duration-700" style="width: {{ max(5, $progress) }}%"></div>
        </div>
        <p class="text-eyebrow text-tertiary mt-2">AI sedang mengekstrak teks dan mengenal pasti medan...</p>
    @endif
</x-app-card>
