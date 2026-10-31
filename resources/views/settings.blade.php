@extends('layouts.app', [
    'title' => 'Tetapan',
    'subtitle' => 'Konfigurasi sistem J-ZAKAT',
])
@section('content')

@php
    $user = auth('web')->user();
    $photo = $user?->profile_photo ? asset('storage/' . $user->profile_photo) : null;
    $initials = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
@endphp

<x-page-header title="Tetapan" subtitle="Konfigurasi sistem J-ZAKAT" eyebrow="Sistem" icon="fa-gear" />

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 stagger">
    <x-app-card class="p-6" :hover="false">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-12 h-12 rounded-[14px] bg-primary text-white flex items-center justify-center overflow-hidden shrink-0 ring-1 ring-primary/10">
                @if($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name ?? 'Administrator' }}" class="w-full h-full object-cover">
                @else
                    <span class="text-[14px] font-semibold">{{ $initials }}</span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-eyebrow text-tertiary">Akaun</p>
                <h2 class="text-h3 text-text mt-0.5">{{ $user->name ?? 'Administrator' }}</h2>
                <p class="text-small text-secondary mt-1">MAIJ — Majlis Agama Islam Johor</p>
            </div>
        </div>
        <div class="soft-panel p-4 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-small text-secondary">Email</span>
                <span class="text-small text-text font-medium">{{ $user->email ?? 'admin@maij.gov.my' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-small text-secondary">Peranan</span>
                <x-status-badge status="primary">Pentadbir</x-status-badge>
            </div>
        </div>
        <x-app-button variant="primary" size="md" icon="fa-pen" class="mt-5" href="{{ route('profile') }}">Edit Profil</x-app-button>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-12 h-12 rounded-[14px] bg-success-soft text-success flex items-center justify-center ring-1 ring-success/10">
                <i class="fa-solid fa-sliders text-[18px]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-eyebrow text-success">Konfigurasi</p>
                <h2 class="text-h3 text-text mt-0.5">Had Kifayah</h2>
                <p class="text-small text-secondary mt-1">Kadar MAIDAM mengikut kategori tanggungan.</p>
            </div>
        </div>
        <x-app-button variant="primary" size="md" icon="fa-arrow-right" href="{{ route('hadkifayah.settings') }}">Buka Tetapan</x-app-button>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <x-section-header title="Sistem" description="Informasi sistem J-ZAKAT" icon="fa-microchip" />
        <div class="space-y-3.5">
            <div><p class="text-eyebrow text-tertiary">Nama Sistem</p><p class="text-body text-text font-medium mt-1">J-ZAKAT Platform</p></div>
            <div><p class="text-eyebrow text-tertiary">Organisasi</p><p class="text-body text-text font-medium mt-1">Majlis Agama Islam Johor</p></div>
            <div><p class="text-eyebrow text-tertiary">Versi</p><p class="text-body text-text font-medium mt-1">v2.0 Enterprise ({{ strtoupper($status['db_driver'] ?? 'mysql') }})</p></div>
            <div><p class="text-eyebrow text-tertiary">Pembekal AI</p><p class="text-body text-text font-medium mt-1">{{ ucfirst($status['ai_provider'] ?? 'mock') }}</p></div>
        </div>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <x-section-header title="Status Perkhidmatan" description="Sambungan sistem semasa" icon="fa-server" />
        <div class="space-y-2.5">
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                <p class="text-body text-text flex-1">Pangkalan Data ({{ strtoupper($status['db_driver'] ?? 'sqlite') }})</p>
                <x-status-badge status="success">{{ $status['db'] ?? 'Aktif' }}</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full @if(($status['firebase'] ?? '') === 'Aktif') bg-success @else bg-danger @endif"></span>
                <p class="text-body text-text flex-1">Firebase Authentication</p>
                <x-status-badge status="@if(($status['firebase'] ?? '') === 'Aktif') success @else danger @endif">{{ $status['firebase'] ?? 'Tidak Dikonfigurasi' }}</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                <p class="text-body text-text flex-1">Enjin Penilaian AI ({{ ucfirst($status['ai_provider'] ?? 'mock') }})</p>
                <x-status-badge status="primary">Aktif</x-status-badge>
            </div>
            <div class="flex items-center gap-3 soft-panel p-3">
                <span class="w-2.5 h-2.5 rounded-full bg-success"></span>
                <p class="text-body text-text flex-1">Cache ({{ $status['cache'] ?? 'Fail' }})</p>
                <x-status-badge status="success">Aktif</x-status-badge>
            </div>
        </div>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <x-section-header title="Enjin AI" description="Konfigurasi penilaian kelayakan" icon="bot" />
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <div class="flex items-center gap-3 soft-panel p-3"><span class="w-7 h-7 rounded-[10px] bg-success text-white flex items-center justify-center"><i class="fa-solid fa-check text-[11px]"></i></span><p class="text-body text-text">Pengesanan Pendua IC</p></div>
            <div class="flex items-center gap-3 soft-panel p-3"><span class="w-7 h-7 rounded-[10px] bg-success text-white flex items-center justify-center"><i class="fa-solid fa-check text-[11px]"></i></span><p class="text-body text-text">Analisis Had Kifayah</p></div>
            <div class="flex items-center gap-3 soft-panel p-3"><span class="w-7 h-7 rounded-[10px] bg-success text-white flex items-center justify-center"><i class="fa-solid fa-check text-[11px]"></i></span><p class="text-body text-text">Skor Kelayakan Automatik</p></div>
            <div class="flex items-center gap-3 soft-panel p-3"><span class="w-7 h-7 rounded-[10px] bg-success text-white flex items-center justify-center"><i class="fa-solid fa-check text-[11px]"></i></span><p class="text-body text-text">Integrasi Data Keluarga</p></div>
        </div>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <x-section-header title="Tentang Aplikasi" description="Kredit dan teknologi" icon="fa-circle-info" />
        <div class="space-y-2 text-body text-text">
            <div class="flex items-center justify-between py-1.5"><span class="text-secondary">Framework</span><span class="font-medium">Laravel + Vite</span></div>
            <div class="flex items-center justify-between py-1.5"><span class="text-secondary">Frontend</span><span class="font-medium">Tailwind 4, Alpine.js, Chart.js</span></div>
            <div class="flex items-center justify-between py-1.5"><span class="text-secondary">Ikon</span><span class="font-medium">Font Awesome 6</span></div>
            <div class="flex items-center justify-between py-1.5"><span class="text-secondary">Tipografi</span><span class="font-medium">Inter / Segoe UI</span></div>
        </div>
        <p class="text-eyebrow text-tertiary mt-4 pt-3 border-t border-divider">© {{ date('Y') }} J-ZAKAT · Sistem Pengurusan Kelayakan Asnaf</p>
    </x-app-card>
</div>
@endsection
