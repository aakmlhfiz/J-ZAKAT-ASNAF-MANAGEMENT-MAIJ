@extends('layouts.app', [
    'title' => 'Dashboard',
    'subtitle' => 'Ringkasan pintar untuk pentadbir MAIJ',
])
@section('content')
@php
    use Carbon\Carbon;
    $hariIni = Carbon::now()->locale('ms')->translatedFormat('l, d F Y');
    $jam = (int) Carbon::now()->format('H');
    $salam = $jam < 12 ? 'Selamat Pagi' : ($jam < 18 ? 'Selamat Tengah Hari' : 'Selamat Petang');
    $user = auth('web')->user();
    $nama = $user?->name ?? 'Pentadbir';
@endphp

<x-page-header
    :title="$salam . ', ' . explode(' ', $nama)[0]"
    :subtitle="$hariIni . ' · Ringkasan pintar untuk pentadbir MAIJ'"
    eyebrow="Dashboard"
    icon="layout-dashboard">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="download">Eksport</x-app-button>
        <x-app-button variant="primary" size="md" icon="user-plus" href="{{ route('profilasnaf') }}">Tambah Pemohon</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 stagger">
    <x-stat-card label="Jumlah Pemohon" :value="number_format($stats['total'] ?? 0)"
        :trend="$stats['total_trend'] ?? '+8.2%'" :trend-up="!str_starts_with($stats['total_trend'] ?? '+', '-')" :trend-label="'bulan ini'"
        icon="users" icon-bg="bg-primary-soft" icon-color="text-primary" :href="route('history')" accent="primary"
        :sparkline="$stats['total_sparkline'] ?? [12,15,13,18,16,20,22,24,23,28,30,32]" />
    <x-stat-card label="Layak" :value="number_format($stats['layak'] ?? 0)"
        :trend="$stats['layak_trend'] ?? '+12.4%'" :trend-up="!str_starts_with($stats['layak_trend'] ?? '+', '-')" :trend-label="'bulan ini'"
        icon="circle-check" icon-bg="bg-success-soft" icon-color="text-success" :href="route('history', ['status' => 'Layak'])" accent="success"
        :sparkline="$stats['layak_sparkline'] ?? [5,7,8,10,9,12,15,14,18,20,22,24]" />
    <x-stat-card label="Tidak Layak" :value="number_format($stats['tidak_layak'] ?? 0)"
        :trend="$stats['tidak_layak_trend'] ?? '-3.1%'" :trend-up="!str_starts_with($stats['tidak_layak_trend'] ?? '-', '-')" :trend-label="'bulan ini'"
        icon="circle-x" icon-bg="bg-danger-soft" icon-color="text-danger" :href="route('history', ['status' => 'Tidak Layak'])" accent="danger"
        :sparkline="$stats['tidak_layak_sparkline'] ?? [10,12,11,9,8,10,8,7,9,6,7,6]" />
    <x-stat-card label="Dalam Semakan" :value="number_format($stats['dalam_semakan'] ?? 0)"
        :trend="$stats['dalam_semakan_trend'] ?? '+2 pemohon'" :trend-up="!str_starts_with($stats['dalam_semakan_trend'] ?? '+', '-')" :trend-label="'minggu ini'"
        icon="clock" icon-bg="bg-warning-soft" icon-color="text-warning" :href="route('history', ['status' => 'Dalam Semakan'])" accent="warning"
        :sparkline="$stats['dalam_semakan_sparkline'] ?? [3,4,3,5,4,6,5,7,6,8,9,8]" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
    <x-app-card class="lg:col-span-2 p-6" :hover="false">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-eyebrow text-tertiary">Trend</p>
                <h2 class="text-h3 text-text mt-1">Permohonan Bulanan</h2>
                <p class="text-small text-secondary mt-1">12 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-small font-semibold {{ str_starts_with($stats['total_trend'] ?? '+', '-') ? 'text-danger' : 'text-success' }}">
                    <x-icon :name="str_starts_with($stats['total_trend'] ?? '+', '-') ? 'trending-down' : 'trending-up'" :size="14" /> {{ $stats['total_trend'] ?? '+12.4%' }}
                </span>
                <x-app-button variant="ghost" size="sm" icon="download">Eksport</x-app-button>
            </div>
        </div>
        <x-trend-chart :data="$values ?? []" :labels="$labels ?? []" color="#3B82F6" height="280" />
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-eyebrow text-tertiary">Taburan</p>
                <h2 class="text-h3 text-text mt-1">Status Pemohon</h2>
            </div>
            <span class="text-small text-tertiary font-medium">{{ $stats['total'] ?? 0 }} jumlah</span>
        </div>
        <x-trend-chart :data="[$stats['layak'] ?? 0, $stats['tidak_layak'] ?? 0, $stats['dalam_semakan'] ?? 0]"
            :labels="['Layak', 'Tidak Layak', 'Dalam Semakan']"
            color="#3B82F6" height="180" type="doughnut" fill="false" />
        <ul class="mt-4 space-y-2.5">
            @foreach([['Layak', $stats['layak'] ?? 0, 'bg-success'], ['Tidak Layak', $stats['tidak_layak'] ?? 0, 'bg-danger'], ['Dalam Semakan', $stats['dalam_semakan'] ?? 0, 'bg-warning']] as [$lbl, $val, $dot])
                <li class="flex items-center justify-between text-small">
                    <span class="flex items-center gap-2 text-text"><span class="w-2.5 h-2.5 rounded-full {{ $dot }}"></span>{{ $lbl }}</span>
                    <span class="text-text font-semibold tabular-nums">{{ number_format($val) }}</span>
                </li>
            @endforeach
        </ul>
    </x-app-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">
    <x-app-card class="lg:col-span-2 p-0" :hover="false">
        <div class="p-6 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-h3 text-text">Permohonan Terkini</h2>
                <p class="text-small text-secondary mt-1">8 rekod paling baharu</p>
            </div>
            <x-app-button variant="ghost" size="sm" icon-right="arrow-right" href="{{ route('viewprofile.index') }}">Lihat semua</x-app-button>
        </div>
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No. IC</th>
                        <th class="text-right">Pendapatan</th>
                        <th>Status</th>
                        <th>Tarikh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latest as $p)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center text-[12px] font-semibold shrink-0">{{ strtoupper(mb_substr($p->nama, 0, 1)) }}</div>
                                    <a href="{{ route('viewprofile', $p->ic) }}" class="font-medium text-text hover:text-primary transition-fast">{{ $p->nama }}</a>
                                </div>
                            </td>
                            <td class="text-secondary tabular-nums">{{ $p->ic }}</td>
                            <td class="text-right tabular-nums">RM {{ number_format((float) $p->pendapatan, 2) }}</td>
                            <td><x-status-badge :status="$p->statusSlug()" /></td>
                            <td class="text-secondary">{{ $p->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <x-empty-state icon="folder-open" title="Tiada profil" description="Tambah permohonan baharu untuk mula.">
                                <x-slot:action>
                                    <x-app-button variant="primary" size="md" icon="user-plus" href="{{ route('profilasnaf') }}">Tambah Pemohon</x-app-button>
                                </x-slot:action>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-app-card>

    <x-app-card class="p-6" :hover="false">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <h2 class="text-h3 text-text">Aktiviti Terkini</h2>
                <p class="text-small text-secondary mt-1">5 pemohon terakhir</p>
            </div>
            <a href="{{ route('history') }}" class="text-small text-primary font-medium hover:underline">Lihat semua</a>
        </div>
        @if($recent->isEmpty())
            <x-empty-state icon="clock" title="Tiada aktiviti" description="Aktiviti terkini akan dipaparkan di sini." />
        @else
            <x-timeline :items="$recent->map(fn($p) => [
                'title' => $p->nama,
                'description' => $p->ic . ' · ' . number_format((float) $p->pendapatan, 2),
                'time' => $p->created_at->diffForHumans(),
                'dotClass' => match($p->statusSlug()) {
                    'layak' => 'bg-success',
                    'tidak-layak' => 'bg-danger',
                    default => 'bg-warning',
                },
            ])->all()" />
        @endif
    </x-app-card>
</div>

<div class="mt-5">
    <x-app-card class="p-6" :hover="false" elevated>
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <h2 class="text-h3 text-text">Tindakan Pantas</h2>
                <p class="text-small text-secondary mt-1">Akses terus ke fungsi yang kerap digunakan</p>
            </div>
        </div>
        @php
            $quickActions = [
                ['route' => 'profilasnaf', 'icon' => 'user-plus', 'label' => 'Daftar Pemohon', 'desc' => 'Tambah profil baharu', 'bg' => 'bg-primary-soft', 'color' => 'text-primary'],
                ['route' => 'hadkifayah', 'icon' => 'calculator', 'label' => 'Kira Had Kifayah', 'desc' => 'Kalkulator MAIDAM', 'bg' => 'bg-success-soft', 'color' => 'text-success'],
                ['route' => 'ai.page', 'icon' => 'sparkles', 'label' => 'AI Assistant', 'desc' => 'Bertanya dengan AI', 'bg' => 'bg-warning-soft', 'color' => 'text-warning'],
                ['route' => 'importcsv', 'icon' => 'file-import', 'label' => 'Import CSV', 'desc' => 'Muat naik pukal', 'bg' => 'bg-info-soft', 'color' => 'text-info'],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($quickActions as $a)
                <a href="{{ route($a['route']) }}" class="group flex flex-col items-start gap-3 p-4 rounded-[14px] bg-card border border-divider hover:border-primary/50 hover:shadow-card transition-apple">
                    <div class="w-10 h-10 rounded-[12px] {{ $a['bg'] }} {{ $a['color'] }} flex items-center justify-center group-hover:scale-110 transition-fast">
                        <x-icon :name="$a['icon']" :size="19" />
                    </div>
                    <div>
                        <p class="text-body text-text font-semibold">{{ $a['label'] }}</p>
                        <p class="text-small text-secondary mt-0.5">{{ $a['desc'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </x-app-card>
</div>
@endsection
