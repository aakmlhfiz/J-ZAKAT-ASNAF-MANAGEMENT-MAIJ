@extends('layouts.app', [
    'title' => 'Kod Undangan Admin',
    'subtitle' => 'Pengurusan kod undangan pendaftaran pentadbir',
])
@section('content')

<x-page-header title="Kod Undangan Admin" subtitle="Pengurusan kod undangan pendaftaran pentadbir" eyebrow="Sistem" icon="fa-ticket">
    <x-slot:actions>
        @if($isSuperAdmin)
            <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('admin.invitations.create') }}">Jana Kod Baharu</x-app-button>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger mb-6">
    <x-stat-card label="Jumlah Kod" :value="number_format($stats['total'])" icon="fa-ticket" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    <x-stat-card label="Aktif" :value="number_format($stats['active'])" icon="fa-circle-check" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    <x-stat-card label="Digunakan" :value="number_format($stats['used'])" icon="fa-check-double" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
    <x-stat-card label="Luput" :value="number_format($stats['expired'])" icon="fa-clock" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
</div>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
        <div class="relative flex-1 max-w-xs">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kod..." class="input-base pl-10 py-2.5 text-[13px]">
        </div>
        <select name="status" class="input-base w-full md:w-40 py-2.5 text-[13px]">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Digunakan</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Luput</option>
            <option value="deactivated" {{ request('status') === 'deactivated' ? 'selected' : '' }}>Dinyahaktifkan</option>
        </select>
        <x-app-button type="submit" variant="secondary" size="md" icon="fa-filter">Tapis</x-app-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-app-button variant="ghost" size="md" icon="fa-xmark" href="{{ route('admin.invitations.index') }}">Reset</x-app-button>
        @endif
    </form>
</x-app-card>

<x-app-card padding="p-0" :hover="false">
    @if($invitations->count() > 0)
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Status</th>
                        <th>Dijana Oleh</th>
                        <th>Digunakan Oleh</th>
                        <th>Luput</th>
                        <th>Tarikh</th>
                        <th class="text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $inv)
                        <tr>
                            <td>
                                <code class="text-[13px] font-mono text-text font-semibold">{{ $inv->code }}</code>
                            </td>
                            <td>
                                <x-status-badge :status="$inv->statusColor()">{{ $inv->statusBadge() }}</x-status-badge>
                            </td>
                            <td class="text-secondary">{{ $inv->creator?->name ?: '—' }}</td>
                            <td class="text-secondary">{{ $inv->user?->name ?: '—' }}</td>
                            <td class="text-secondary">{{ $inv->expires_at ? $inv->expires_at->format('d M Y') : '—' }}</td>
                            <td class="text-secondary whitespace-nowrap">{{ $inv->created_at->format('d M Y') }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.invitations.show', $inv) }}" class="btn btn-ghost btn-icon btn-sm" title="Lihat"><i class="fa-solid fa-eye text-[12px]"></i></a>
                                    @if(!$inv->used_at && $isSuperAdmin)
                                        @if($inv->is_active)
                                            <form method="POST" action="{{ route('admin.invitations.deactivate', $inv) }}" class="inline" onsubmit="return confirm('Nyahaktifkan kod ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-icon btn-sm hover:!text-warning" title="Nyahaktifkan"><i class="fa-solid fa-ban text-[12px]"></i></button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.invitations.reactivate', $inv) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-ghost btn-icon btn-sm hover:!text-success" title="Aktifkan"><i class="fa-solid fa-circle-check text-[12px]"></i></button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.invitations.destroy', $inv) }}" class="inline" onsubmit="return confirm('Padam kod ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-icon btn-sm hover:!text-danger" title="Padam"><i class="fa-solid fa-trash text-[12px]"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$invitations" />
    @else
        <x-empty-state icon="fa-ticket" title="Tiada kod undangan" description="Jana kod undangan pertama untuk mula.">
            <x-slot:action>
                <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('admin.invitations.create') }}">Jana Kod Baharu</x-app-button>
            </x-slot:action>
        </x-empty-state>
    @endif
</x-app-card>

@endsection
