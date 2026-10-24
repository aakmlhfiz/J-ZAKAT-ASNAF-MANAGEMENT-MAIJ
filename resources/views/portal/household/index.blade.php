@extends('portal.layouts.app')
@section('title', 'Isi Rumah Saya — Portal Pemohon')
@section('page-title', 'Isi Rumah Saya')

@section('content')
<x-page-header
    title="Isi Rumah Saya"
    subtitle="Senarai ahli isi rumah yang berkaitan dengan No. Kad Pengenalan anda."
/>

@if(!$household)
    <x-app-card>
        <x-empty-state
            icon="fa-house-chimney-user"
            title="Tiada rekod isi rumah"
            description="Maklumat isi rumah akan muncul selepas permohonan anda diproses atau anda mengimbas Borang Bantuan MAIJ."
        >
            <x-slot:action>
                <x-app-button variant="primary" size="md" icon="fa-file-circle-plus" :href="route('user.applications.create')">
                    Mohon Bantuan
                </x-app-button>
            </x-slot:action>
        </x-empty-state>
    </x-app-card>
@else
    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger">
        <x-stat-card label="Jumlah Ahli" :value="$summary['total_members']" icon="fa-users" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
        <x-stat-card label="Jumlah Tanggungan" :value="$summary['total_tanggungan']" icon="fa-people-roof" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
        <x-stat-card label="Pendapatan Isi Rumah" :value="'RM '.number_format($summary['total_income'], 2)" icon="fa-money-bill-wave" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
        <x-stat-card label="Komitmen Bulanan" :value="'RM '.number_format($summary['total_expenses'], 2)" icon="fa-receipt" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
    </div>

    {{-- Ketua Keluarga --}}
    @if($ketua)
    <x-app-card class="p-6 mb-6" :hover="false">
        <x-section-header title="Ketua Keluarga" description="Ketua isi rumah" icon="fa-user-check" />
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><p class="text-eyebrow text-tertiary">Nama</p><p class="text-body text-text font-semibold mt-1">{{ $ketua->nama }}</p></div>
            <div><p class="text-eyebrow text-tertiary">No. IC</p><p class="text-body text-text mt-1 tabular-nums">{{ $ketua->ic ?: '—' }}</p></div>
            <div><p class="text-eyebrow text-tertiary">Pendapatan</p><p class="text-body text-text mt-1">RM {{ number_format((float) $ketua->pendapatan_bulanan, 2) }}</p></div>
        </div>
    </x-app-card>
    @endif

    {{-- Ahli Isi Rumah --}}
    <x-app-card class="p-0" :hover="false">
        <div class="px-6 pt-5 pb-3 border-b border-divider">
            <x-section-header title="Ahli Isi Rumah" :description="$summary['total_members'].' ahli keseluruhan'" icon="fa-users" />
        </div>
        <div class="divide-y divide-divider">
            @forelse($members as $member)
                @php
                    $assistanceCount = ($assistanceByMember[$member->id] ?? collect())->count();
                    $tanggungan = App\Services\PortalHouseholdService::isTanggungan($member);
                    $pelajar = App\Services\PortalHouseholdService::isStudent($member);
                @endphp
                <div class="px-6 py-5">
                    <div class="flex items-start gap-4 flex-wrap">
                        <div class="w-11 h-11 rounded-full bg-primary-soft text-primary flex items-center justify-center font-semibold shrink-0">
                            {{ strtoupper(mb_substr($member->nama, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-body text-text font-semibold">{{ $member->nama }}</p>
                                <x-status-badge :status="$member->hubungan === 'Ketua Isi Rumah' ? 'primary' : 'secondary'">{{ $member->hubungan }}</x-status-badge>
                                @if($tanggungan)<x-status-badge status="warning">Tanggungan</x-status-badge>@endif
                                @if($pelajar)<x-status-badge status="info">Pelajar</x-status-badge>@endif
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-5 gap-y-2 mt-3 text-small">
                                <div><span class="text-tertiary">No. IC:</span> <span class="text-text tabular-nums">{{ $member->ic ?: '—' }}</span></div>
                                <div><span class="text-tertiary">Umur:</span> <span class="text-text">{{ $member->umur ? $member->umur.' tahun' : '—' }}</span></div>
                                <div><span class="text-tertiary">Pekerjaan:</span> <span class="text-text">{{ $member->pekerjaan ?: '—' }}</span></div>
                                <div><span class="text-tertiary">Pendapatan:</span> <span class="text-text">RM {{ number_format((float) $member->pendapatan_bulanan, 2) }}</span></div>
                                <div><span class="text-tertiary">Kesihatan:</span> <span class="text-text">{{ $member->status_kesihatan ?: '—' }}</span></div>
                                <div><span class="text-tertiary">Bantuan aktif:</span> <span class="text-text">{{ $assistanceCount }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12">
                    <x-empty-state icon="fa-users-slash" title="Tiada ahli direkodkan" description="Ahli isi rumah akan muncul di sini." />
                </div>
            @endforelse
        </div>
    </x-app-card>
@endif
@endsection
