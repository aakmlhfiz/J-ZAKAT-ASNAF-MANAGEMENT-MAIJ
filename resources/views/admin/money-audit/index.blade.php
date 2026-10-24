@extends('layouts.app', [
    'title' => 'Audit Kewangan',
    'subtitle' => 'Pengauditan transaksi bantuan & pergerakan wang J-ZAKAT',
])

@section('content')

<x-page-header title="Audit Kewangan" subtitle="Pengauditan transaksi bantuan & pergerakan wang J-ZAKAT" eyebrow="Pentadbiran" icon="fa-money-check-dollar">
    <x-slot:actions>
        <a href="{{ route('admin.money-audit.export', request()->only(['jenis_bantuan', 'status', 'frequency', 'from', 'to', 'search'])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-fast">
            <i class="fa-solid fa-file-export"></i>
            <span>Eksport CSV</span>
        </a>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger mb-6">
    <x-stat-card label="Jumlah Diluluskan (RM)" :value="number_format($stats['total_amount'], 2)" icon="fa-sack-dollar" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    <x-stat-card label="Bilangan Rekod" :value="number_format($stats['total_records'])" icon="fa-receipt" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    <x-stat-card label="Bulan Ini (RM)" :value="number_format($stats['month_amount'], 2)" icon="fa-calendar-week" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
    <x-stat-card label="Isi Rumah Terlibat" :value="number_format($stats['distinct_households'])" icon="fa-house-chimney-user" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
</div>

@if($byJenis->isNotEmpty())
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @foreach($byJenis as $row)
    <div class="surface p-4">
        <p class="text-eyebrow text-tertiary truncate">{{ $row->jenis_bantuan }}</p>
        <p class="text-lg font-bold text-text mt-1 tabular-nums">RM {{ number_format((float) $row->amount, 2) }}</p>
        <p class="text-[11px] text-tertiary mt-1">{{ $row->count }} rekod</p>
    </div>
    @endforeach
</div>
@endif

@php
    $tab = $tab ?? 'transaksi';
    $tabQuery = fn($t) => array_merge(request()->query(), ['tab' => $t]);
@endphp

<div class="flex items-center gap-1 mb-5 border-b border-border">
    <a href="{{ route('admin.money-audit.index', $tabQuery('transaksi')) }}"
       class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-fast {{ $tab === 'transaksi' ? 'border-primary text-primary' : 'border-transparent text-text-secondary hover:text-text' }}">
        <i class="fa-solid fa-receipt mr-1"></i> Transaksi
    </a>
    <a href="{{ route('admin.money-audit.index', $tabQuery('trail')) }}"
       class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-fast {{ $tab === 'trail' ? 'border-primary text-primary' : 'border-transparent text-text-secondary hover:text-text' }}">
        <i class="fa-solid fa-clipboard-list mr-1"></i> Jejak Audit
    </a>
</div>

@if($tab === 'trail')
<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <input type="hidden" name="tab" value="trail">
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Cari</label>
            <input type="text" name="trail_search" value="{{ request('trail_search') }}" placeholder="Emel, IP, aksi..."
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Pengguna</label>
            <select name="trail_user_id" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('trail_user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Aksi</label>
            <select name="trail_action" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($trailActions as $a)
                    <option value="{{ $a }}" @selected(request('trail_action') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Dari</label>
            <input type="datetime-local" name="trail_from" value="{{ request('trail_from') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Hingga</label>
            <input type="datetime-local" name="trail_to" value="{{ request('trail_to') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div class="md:col-span-3 lg:col-span-6 flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90">
                <i class="fa-solid fa-filter mr-1"></i> Tapis
            </button>
            <a href="{{ route('admin.money-audit.index', ['tab' => 'trail']) }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-muted">
                Reset
            </a>
        </div>
    </form>
</x-app-card>

<x-app-card :hover="false" class="p-0 overflow-hidden">
    <x-app-table>
        <x-slot name="head">
            <tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Tarikh / Masa</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Pengguna</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Aksi</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Keterangan</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Metadata</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">IP</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse($trail as $log)
            <tr class="hover:bg-muted/30">
                <td class="px-4 py-3 text-sm whitespace-nowrap">
                    <div class="font-medium">{{ $log->created_at?->format('d M Y') }}</div>
                    <div class="text-xs text-text-tertiary">{{ $log->created_at?->format('H:i:s') }}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $log->user_email ?? '—' }}</div>
                    <div class="mt-1">
                        <x-status-badge :color="$log->user_role === 'admin' ? 'primary' : 'info'">
                            {{ $log->user_role ?? 'system' }}
                        </x-status-badge>
                    </div>
                </td>
                <td class="px-4 py-3 text-[11px] font-mono text-text-secondary whitespace-nowrap">
                    {{ $log->action }}
                </td>
                <td class="px-4 py-3 text-sm max-w-md">
                    <div>{{ $log->description }}</div>
                </td>
                <td class="px-4 py-3 text-sm max-w-md">
                    @if($log->metadata)
                        @php $m = $log->metadata; @endphp
                        <div class="text-xs text-text-secondary">
                            <div><span class="text-text-tertiary">Jenis:</span> {{ $m['jenis_bantuan'] ?? '—' }}</div>
                            <div><span class="text-text-tertiary">Amaun:</span> RM {{ isset($m['amount']) ? number_format((float) $m['amount'], 2) : '—' }}</div>
                            <div><span class="text-text-tertiary">Rujukan:</span> {{ $m['reference_number'] ?? '—' }}</div>
                            @if(!empty($m['changes']))
                                <details class="mt-1 text-xs text-text-tertiary">
                                    <summary class="cursor-pointer hover:text-primary">Lihat perubahan</summary>
                                    <pre class="mt-1 p-2 bg-muted/50 rounded text-[11px] overflow-x-auto">{{ json_encode($m['changes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </div>
                    @else
                        —
                    @endif
                </td>
                <td class="px-4 py-3 text-[11px] font-mono">{{ $log->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-text-tertiary">
                    <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                    <div>Tiada jejak audit kewangan dijumpai.</div>
                </td>
            </tr>
            @endforelse
        </x-slot>
    </x-app-table>

    <div class="p-4 border-t border-border">
        {{ $trail->links('components.pagination') }}
    </div>
</x-app-card>
@else
<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rujukan, pegawai, ahli, isi rumah..."
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Jenis Bantuan</label>
            <select name="jenis_bantuan" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($jenisOptions as $j)
                    <option value="{{ $j }}" @selected(request('jenis_bantuan') === $j)>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                <option value="approved" @selected(request('status') === 'approved')>Diluluskan</option>
                <option value="pending" @selected(request('status') === 'pending')>Dalam Proses</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Kekerapan</label>
            <select name="frequency" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                <option value="one_off" @selected(request('frequency') === 'one_off')>Sekali (One-off)</option>
                <option value="monthly" @selected(request('frequency') === 'monthly')>Bulanan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Hingga</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div class="md:col-span-3 lg:col-span-6 flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90">
                <i class="fa-solid fa-filter mr-1"></i> Tapis
            </button>
            <a href="{{ route('admin.money-audit.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-muted">
                Reset
            </a>
        </div>
    </form>
</x-app-card>

<x-app-card :hover="false" class="p-0 overflow-hidden">
    <x-app-table>
        <x-slot name="head">
            <tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Tarikh Lulus</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Isi Rumah</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Ahli</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Jenis Bantuan</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Amaun</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Pegawai</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">No Rujukan</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse($ledger as $row)
            <tr class="hover:bg-muted/30">
                <td class="px-4 py-3 text-sm whitespace-nowrap">
                    {{ $row->approved_date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $row->household?->household_id ?? '—' }}</div>
                    <div class="text-xs text-text-tertiary">{{ $row->household?->daerah ?? '' }}</div>
                </td>
                <td class="px-4 py-3 text-sm">{{ $row->member?->nama ?? '—' }}</td>
                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $row->jenis_bantuan }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-right tabular-nums whitespace-nowrap">
                    RM {{ number_format((float) $row->amount, 2) }}
                    @if($row->frequency === 'monthly')
                        <span class="text-xs text-text-tertiary font-normal">/bln</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :color="match($row->status) { 'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'neutral' }">
                        {{ match($row->status) { 'approved' => 'Diluluskan', 'pending' => 'Dalam Proses', 'rejected' => 'Ditolak', default => $row->status } }}
                    </x-status-badge>
                </td>
                <td class="px-4 py-3 text-sm">{{ $row->approval_officer ?? '—' }}</td>
                <td class="px-4 py-3 text-[11px] font-mono">{{ $row->reference_number ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-text-tertiary">
                    <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                    <div>Tiada transaksi bantuan dijumpai.</div>
                </td>
            </tr>
            @endforelse
        </x-slot>
    </x-app-table>

    <div class="p-4 border-t border-border">
        {{ $ledger->links('components.pagination') }}
    </div>
</x-app-card>
@endif

@endsection