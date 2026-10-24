@extends('layouts.app', [
    'title' => 'Kod Undangan: ' . $invitation->code,
    'subtitle' => 'Butiran kod undangan pendaftaran pentadbir',
])
@section('content')

<x-page-header :title="$invitation->code" subtitle="Butiran kod undangan pendaftaran pentadbir" eyebrow="Sistem" icon="fa-ticket">
    <x-slot:actions>
        @if($isSuperAdmin)
            @if(!$invitation->used_at)
                @if($invitation->is_active)
                    <form method="POST" action="{{ route('admin.invitations.deactivate', $invitation) }}" class="inline" onsubmit="return confirm('Nyahaktifkan kod ini?')">
                        @csrf
                        <x-app-button type="submit" variant="warning" size="md" icon="fa-ban">Nyahaktifkan</x-app-button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.invitations.reactivate', $invitation) }}" class="inline">
                        @csrf
                        <x-app-button type="submit" variant="success" size="md" icon="fa-circle-check">Aktifkan Semula</x-app-button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" class="inline" onsubmit="return confirm('Padam kod ini? Tindakan ini tidak boleh dibatalkan.')">
                    @csrf @method('DELETE')
                    <x-app-button type="submit" variant="danger" size="md" icon="fa-trash">Padam</x-app-button>
                </form>
            @endif
        @endif
        <x-app-button variant="secondary" size="md" icon="fa-arrow-left" href="{{ route('admin.invitations.index') }}">Kembali</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Maklumat Kod" icon="fa-info-circle" />
            <div class="space-y-4 max-w-md">
                <div>
                    <p class="text-eyebrow text-tertiary">Kod Undangan</p>
                    <div class="flex items-center gap-3 mt-1.5" x-data="invitationCopy()">
                        <code class="text-[16px] font-mono text-text font-bold tracking-wide">{{ $invitation->code }}</code>
                        <button @click="copy('{{ $invitation->code }}')"
                            class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-fast"
                            title="Salin kod">
                            <i class="fa-solid" :class="copied ? 'fa-check text-success' : 'fa-copy'"></i>
                        </button>
                    </div>
                </div>
                <div><p class="text-eyebrow text-tertiary">Status</p><p class="text-body text-text font-medium mt-1.5"><x-status-badge :status="$invitation->statusColor()">{{ $invitation->statusBadge() }}</x-status-badge></p></div>
                <div><p class="text-eyebrow text-tertiary">Dijana Oleh</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->creator?->name ?: '—' }}</p></div>
                <div><p class="text-eyebrow text-tertiary">Dijana Pada</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->created_at->format('d M Y, H:i') }}</p></div>
                <div><p class="text-eyebrow text-tertiary">Tarikh Luput</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->expires_at ? $invitation->expires_at->format('d M Y, H:i') : 'Tidak luput' }}</p></div>
                @if($invitation->notes)
                    <div><p class="text-eyebrow text-tertiary">Nota</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->notes }}</p></div>
                @endif
            </div>
        </x-app-card>

        @if($invitation->used_at)
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Maklumat Penggunaan" icon="fa-user-check" />
                <div class="space-y-4 max-w-md">
                    <div><p class="text-eyebrow text-tertiary">Digunakan Oleh</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->user?->name ?: '—' }}</p></div>
                    <div><p class="text-eyebrow text-tertiary">Emel</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->user?->email ?: '—' }}</p></div>
                    <div><p class="text-eyebrow text-tertiary">Digunakan Pada</p><p class="text-body text-text font-medium mt-1.5">{{ $invitation->used_at->format('d M Y, H:i') }}</p></div>
                </div>
            </x-app-card>
        @endif
    </div>

    <div class="space-y-6">
        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header title="Cara Penggunaan" icon="fa-circle-question" />
            <ol class="space-y-3 text-small">
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-primary-soft text-primary text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                    <span class="text-text">Salin kod undangan di atas.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-primary-soft text-primary text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                    <span class="text-text">Kongsi kod dengan pentadbir yang ingin mendaftar.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-primary-soft text-primary text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                    <span class="text-text">Pentadbir mendaftar di <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">/register</a> dengan kod ini.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-primary-soft text-primary text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">4</span>
                    <span class="text-text">Kod akan ditanda sebagai digunakan secara automatik.</span>
                </li>
            </ol>
        </x-app-card>

        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Timeline" icon="fa-timeline" />
            <ul class="relative pl-6">
                <span class="absolute left-2 top-2 bottom-2 w-px bg-divider"></span>
                @if($invitation->used_at)
                    <li class="relative pb-5">
                        <span class="absolute -left-[18px] top-1.5 w-3 h-3 rounded-full bg-info ring-4 ring-card"></span>
                        <p class="text-body text-text font-medium">Digunakan</p>
                        <p class="text-small text-secondary mt-1">{{ $invitation->user?->name ?: '—' }} mendaftar</p>
                        <p class="text-eyebrow text-tertiary mt-1">{{ $invitation->used_at->format('d M Y, H:i') }}</p>
                    </li>
                @endif
                <li class="relative pb-5">
                    <span class="absolute -left-[18px] top-1.5 w-3 h-3 rounded-full bg-primary ring-4 ring-card"></span>
                    <p class="text-body text-text font-medium">Dijana</p>
                    <p class="text-small text-secondary mt-1">Kod dicipta oleh {{ $invitation->creator?->name ?: 'Sistem' }}</p>
                    <p class="text-eyebrow text-tertiary mt-1">{{ $invitation->created_at->format('d M Y, H:i') }}</p>
                </li>
            </ul>
        </x-app-card>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('invitationCopy', () => ({
        copied: false,
        async copy(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch {
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        },
    }));
});
</script>
@endpush

@endsection
