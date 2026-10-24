@extends('layouts.app', [
    'title' => 'Senarai Permohonan',
    'subtitle' => 'Pengurusan & carian permohonan',
])
@section('content')
@php
    $current = request('status');
    $search  = request('search');
    $filters = [
        ''                 => ['label' => 'Semua',     'tone' => 'primary', 'count' => $totals['all']         ?? null],
        'Layak'            => ['label' => 'Layak',     'tone' => 'success', 'count' => $totals['layak']       ?? null],
        'Tidak Layak'      => ['label' => 'Tidak Layak','tone' => 'danger',  'count' => $totals['tidak_layak'] ?? null],
        'Dalam Semakan'    => ['label' => 'Semakan',   'tone' => 'warning', 'count' => $totals['semakan']     ?? null],
    ];
@endphp

<x-page-header title="Senarai Permohonan" subtitle="{{ $items->total() }} rekod dijumpai" eyebrow="Permohonan" icon="fa-table-list">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-import" href="{{ route('importcsv') }}">Import CSV</x-app-button>
        <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('profilasnaf') }}">Tambah</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, No. IC, atau IC Ketua Rumah…" class="input-base pl-10 py-2.5 text-[13px]">
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($filters as $value => $f)
                @php
                    $isActive = ($current ?? '') === $value;
                    $href = $value === '' ? route('history', ['search' => $search]) : route('history', ['status' => $value, 'search' => $search]);
                @endphp
                <a href="{{ $href }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-pill text-[13px] font-semibold transition-apple {{ $isActive ? 'bg-primary text-white shadow-sm' : 'bg-muted text-text hover:bg-divider' }}">
                    @if(!$isActive)
                        <span class="w-1.5 h-1.5 rounded-full
                            @switch($f['tone'])
                                @case('success') bg-success @break
                                @case('danger')  bg-danger  @break
                                @case('warning') bg-warning @break
                                @default         bg-secondary
                            @endswitch
                        "></span>
                    @endif
                    <span>{{ $f['label'] }}</span>
                    @if(!empty($f['count']))<span class="opacity-70 tabular-nums">{{ $f['count'] }}</span>@endif
                </a>
            @endforeach
        </div>
    </form>
</x-app-card>

<x-app-card padding="p-0" :hover="false">
    <div class="overflow-x-auto">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>No. IC</th>
                    <th>IC Ketua Rumah</th>
                    <th class="text-right">Pendapatan</th>
                    <th class="text-right">Had Kifayah</th>
                    <th>Status</th>
                    <th>Tarikh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $p)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center text-[12px] font-semibold shrink-0">{{ strtoupper(mb_substr($p->nama, 0, 1)) }}</div>
                                <a href="{{ route('viewprofile', $p->ic) }}" class="font-medium text-text hover:text-primary transition-fast">{{ $p->nama }}</a>
                            </div>
                        </td>
                        <td class="text-secondary tabular-nums">{{ $p->ic }}</td>
                        <td class="text-secondary tabular-nums">{{ $p->ic_ketua_rumah ?: '—' }}</td>
                        <td class="text-right tabular-nums">RM {{ number_format((float) $p->pendapatan, 2) }}</td>
                        <td class="text-right tabular-nums">RM {{ number_format((float) $p->had_kifayah, 2) }}</td>
                        <td><x-status-badge :status="$p->statusSlug()" /></td>
                        <td class="text-secondary">{{ $p->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <x-empty-state icon="fa-folder-open" title="Tiada rekod dijumpai" :description="($search || $current) ? 'Cuba ubah carian atau penapis anda.' : 'Tambah permohonan baharu untuk mula.'" />
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$items" />
</x-app-card>
@endsection
