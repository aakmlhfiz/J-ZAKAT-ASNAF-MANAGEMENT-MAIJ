@extends('layouts.app', [
    'title' => 'Audit Log',
    'subtitle' => 'Rekod aktiviti sistem J-ZAKAT AI',
])

@section('content')

<x-page-header title="Audit Log" subtitle="Rekod aktiviti sistem J-ZAKAT AI" eyebrow="Pentadbiran" icon="fa-clipboard-list">
    <x-slot:actions>
        <a href="{{ route('admin.audit-logs.export', request()->only(['user_id', 'role', 'category', 'action', 'from', 'to', 'search'])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-fast">
            <i class="fa-solid fa-file-export"></i>
            <span>Eksport CSV</span>
        </a>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger mb-6">
    <x-stat-card label="Jumlah Rekod" :value="number_format($stats['total'])" icon="fa-database" icon-bg="bg-primary-soft" icon-color="text-primary" accent="primary" />
    <x-stat-card label="Aktiviti Hari Ini" :value="number_format($stats['today'])" icon="fa-calendar-day" icon-bg="bg-success-soft" icon-color="text-success" accent="success" />
    <x-stat-card label="Log Masuk Hari Ini" :value="number_format($stats['logins_today'])" icon="fa-right-to-bracket" icon-bg="bg-info-soft" icon-color="text-info" accent="info" />
    <x-stat-card label="Pengguna Unik Hari Ini" :value="number_format($stats['unique_users'])" icon="fa-users" icon-bg="bg-warning-soft" icon-color="text-warning" accent="warning" />
</div>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Emel, IP, aksi..."
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Pengguna</label>
            <select name="user_id" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Peranan</label>
            <select name="role" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Kategori</label>
            <select name="category" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($categories as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Aksi</label>
            <select name="action" class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                <option value="">Semua</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Dari</label>
            <input type="datetime-local" name="from" value="{{ request('from') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-text-secondary mb-1">Hingga</label>
            <input type="datetime-local" name="to" value="{{ request('to') }}"
                   class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
        </div>
        <div class="md:col-span-3 lg:col-span-6 flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90">
                <i class="fa-solid fa-filter mr-1"></i> Tapis
            </button>
            <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-muted">
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
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Aksi</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Keterangan</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">IP</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Pelayar · OS</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-text-secondary uppercase tracking-wider">Peranti</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse($logs as $log)
            <tr class="hover:bg-muted/30">
                <td class="px-4 py-3 text-sm whitespace-nowrap">
                    <div class="font-medium">{{ $log->created_at?->format('d M Y') }}</div>
                    <div class="text-xs text-text-tertiary">{{ $log->created_at?->format('H:i:s') }}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">{{ $log->user_email ?? '—' }}</div>
                    <div class="mt-1">
                        <x-status-badge :color="$log->user_role === 'admin' ? 'primary' : ($log->user_role === 'portal' ? 'info' : 'neutral')">
                            {{ $log->user_role ?? 'system' }}
                        </x-status-badge>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <x-status-badge :color="match($log->category) { 'auth' => 'info', 'application' => 'primary', 'data' => 'success', 'security' => 'warning', 'system' => 'neutral', default => 'neutral' }">
                        {{ $log->categoryLabel() }}
                    </x-status-badge>
                </td>
                <td class="px-4 py-3 text-[11px] font-mono text-text-secondary whitespace-nowrap">
                    {{ $log->action }}
                </td>
                <td class="px-4 py-3 text-sm max-w-md">
                    <div>{{ $log->description }}</div>
                    @if($log->metadata)
                        <details class="mt-1 text-xs text-text-tertiary">
                            <summary class="cursor-pointer hover:text-primary">Lihat metadata</summary>
                            <pre class="mt-1 p-2 bg-muted/50 rounded text-[11px] overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif
                </td>
                <td class="px-4 py-3 text-[11px] font-mono">{{ $log->ip_address ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">
                    <div>{{ $log->browser ?? '—' }}</div>
                    <div class="text-text-tertiary">{{ $log->os ?? '' }}</div>
                </td>
                <td class="px-4 py-3 text-xs">{{ $log->device ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-text-tertiary">
                    <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                    <div>Tiada rekod audit log dijumpai.</div>
                </td>
            </tr>
            @endforelse
        </x-slot>
    </x-app-table>

    <div class="p-4 border-t border-border">
        {{ $logs->links('components.pagination') }}
    </div>
</x-app-card>

@endsection
