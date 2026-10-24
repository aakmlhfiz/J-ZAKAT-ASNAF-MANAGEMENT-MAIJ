@extends('layouts.app', [
    'title' => 'Senarai Asnaf',
    'subtitle' => 'Pengurusan & carian pemohon bantuan',
])
@section('content')
<x-page-header title="Senarai Asnaf" subtitle="Pengurusan & carian pemohon bantuan" eyebrow="Profil Asnaf" icon="fa-id-card">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-import" href="{{ route('importcsv') }}">Import CSV</x-app-button>
        <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('profilasnaf') }}">Tambah Permohonan</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 mb-6 stagger">
    <div class="surface p-4">
        <p class="text-eyebrow text-tertiary">Jumlah</p>
        <p class="text-metric-sm text-text mt-1.5 tabular-nums">{{ number_format($items->total() ?? count($items ?? [])) }}</p>
    </div>
    <div class="surface p-4">
        <p class="text-eyebrow text-success">Layak</p>
        <p class="text-metric-sm text-success mt-1.5 tabular-nums">{{ number_format($statusCounts['layak'] ?? 0) }}</p>
    </div>
    <div class="surface p-4">
        <p class="text-eyebrow text-danger">Tidak Layak</p>
        <p class="text-metric-sm text-danger mt-1.5 tabular-nums">{{ number_format($statusCounts['tidak_layak'] ?? 0) }}</p>
    </div>
    <div class="surface p-4">
        <p class="text-eyebrow text-warning">Semakan</p>
        <p class="text-metric-sm text-warning mt-1.5 tabular-nums">{{ number_format($statusCounts['dalam_semakan'] ?? 0) }}</p>
    </div>
</div>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, No. IC…" class="input-base pl-10 py-2.5 text-[13px]">
        </div>
        <x-app-button type="submit" variant="secondary" size="md" icon="fa-magnifying-glass">Cari</x-app-button>
    </form>
</x-app-card>

<x-app-card padding="p-0" :hover="false">
    <div class="overflow-x-auto" x-data="{ selected: [] }">
        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-3 px-6 py-3 bg-primary-soft/50 border-b border-primary-soft">
            <span class="text-sm font-medium text-primary" x-text="selected.length + ' dipilih'"></span>
            <button @click="if (confirm('Adakah anda pasti untuk memadam ' + selected.length + ' profil terpilih?')) $refs.bulkDeleteForm.submit()"
                class="btn btn-destructive btn-sm">
                <i class="fa-solid fa-trash mr-1.5"></i> Padam Terpilih
            </button>
        </div>

        <form x-ref="bulkDeleteForm" method="POST" action="{{ route('profilasnaf.bulk-destroy') }}" class="hidden">
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <table class="app-table">
            <thead>
                <tr>
                    <th class="w-10">
                        <input type="checkbox"
                            :checked="selected.length > 0 && $root.querySelectorAll('.bulk-check').length > 0 && selected.length === $root.querySelectorAll('.bulk-check').length"
                            @click.prevent="
                                const cbs = [...$root.querySelectorAll('.bulk-check')];
                                if (cbs.length === 0) return;
                                const allChecked = selected.length === cbs.length;
                                if (allChecked) {
                                    cbs.forEach(cb => { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); });
                                } else {
                                    cbs.forEach(cb => { cb.checked = true; cb.dispatchEvent(new Event('change', { bubbles: true })); });
                                }
                            ">
                    </th>
                    <th>Nama</th>
                    <th>No. IC</th>
                    <th>Telefon</th>
                    <th class="text-right">Pendapatan</th>
                    <th class="text-right">Had Kifayah</th>
                    <th>Status</th>
                    <th>Tarikh</th>
                    <th class="text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $p)
                    <tr :class="selected.includes('{{ $p->id }}') && 'is-selected'">
                        <td>
                            <input type="checkbox" class="bulk-check" value="{{ $p->id }}" x-model="selected">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-soft text-primary flex items-center justify-center text-[12px] font-semibold shrink-0">{{ strtoupper(mb_substr($p->nama, 0, 1)) }}</div>
                                <a href="{{ route('viewprofile', $p->ic) }}" class="font-medium text-text hover:text-primary transition-fast">{{ $p->nama }}</a>
                            </div>
                        </td>
                        <td class="text-secondary tabular-nums">{{ $p->ic }}</td>
                        <td class="text-secondary tabular-nums">{{ $p->telefon ?: '—' }}</td>
                        <td class="text-right tabular-nums">RM {{ number_format((float) $p->pendapatan, 2) }}</td>
                        <td class="text-right tabular-nums">RM {{ number_format((float) $p->had_kifayah, 2) }}</td>
                        <td>
                            <div class="flex items-center gap-1.5">
                                <x-status-badge :status="$p->statusSlug()" />
                                @if ($p->isApproved())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-success bg-success/10 px-1.5 py-0.5 rounded-full"><i class="fa-solid fa-check text-[9px]"></i> Lulus</span>
                                @elseif ($p->isRejected())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-danger bg-danger/10 px-1.5 py-0.5 rounded-full"><i class="fa-solid fa-xmark text-[9px]"></i> Tolak</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-secondary">{{ $p->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viewprofile', $p->ic) }}" class="btn btn-ghost btn-icon btn-sm" title="Lihat"><i class="fa-solid fa-eye text-[12px]"></i></a>
                                <a href="{{ route('profilasnaf.edit', $p->ic) }}" class="btn btn-ghost btn-icon btn-sm" title="Edit"><i class="fa-solid fa-pen text-[12px]"></i></a>
                                @if ($p->letter_path)
                                    <a href="{{ route('viewprofile.letter', $p->ic) }}" class="btn btn-ghost btn-icon btn-sm hover:!text-success" title="Muat Turun Surat"><i class="fa-solid fa-file-pdf text-[12px]"></i></a>
                                @endif
                                @if ($p->isPendingApproval())
                                    <button type="button" @click="$dispatch('open-confirm', { title: 'Luluskan Permohonan', message: 'Adakah anda pasti untuk meluluskan permohonan {{ addslashes($p->nama) }}?', confirmText: 'Lulus', variant: 'success' })" @confirmed.window="document.getElementById('approveForm{{ $p->id }}').submit();" class="btn btn-ghost btn-icon btn-sm hover:!text-success" title="Lulus"><i class="fa-solid fa-check text-[12px]"></i></button>
                                    <button type="button" @click="$dispatch('open-confirm', { title: 'Tolak Permohonan', message: 'Adakah anda pasti untuk menolak permohonan {{ addslashes($p->nama) }}?', confirmText: 'Tolak', variant: 'destructive' })" @confirmed.window="document.getElementById('rejectForm{{ $p->id }}').submit();" class="btn btn-ghost btn-icon btn-sm hover:!text-danger" title="Tolak"><i class="fa-solid fa-xmark text-[12px]"></i></button>
                                    <form id="approveForm{{ $p->id }}" method="POST" action="{{ route('viewprofile.approve', $p->ic) }}" class="hidden">@csrf</form>
                                    <form id="rejectForm{{ $p->id }}" method="POST" action="{{ route('viewprofile.reject', $p->ic) }}" class="hidden">@csrf</form>
                                @endif
                                <button type="button" @click="$dispatch('open-confirm', { title: 'Padam Profil', message: 'Adakah anda pasti untuk memadam profil {{ addslashes($p->nama) }}? Tindakan ini tidak boleh dibatalkan.', confirmText: 'Padam', variant: 'destructive' })" @confirmed.window="document.getElementById('delForm{{ $p->id }}').submit();" class="btn btn-ghost btn-icon btn-sm hover:!text-danger" title="Padam"><i class="fa-solid fa-trash text-[12px]"></i></button>
                                <form id="delForm{{ $p->id }}" method="POST" action="{{ route('profilasnaf.destroy', $p->ic) }}" class="hidden">@csrf @method('DELETE')</form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <x-empty-state icon="fa-folder-open" title="Tiada profil dijumpai" :description="$search ? 'Cuba ubah carian anda.' : 'Tambah permohonan baharu untuk mula.'">
                            <x-slot:action>
                                <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('profilasnaf') }}">Tambah Permohonan</x-app-button>
                            </x-slot:action>
                        </x-empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$items" />
</x-app-card>
@endsection
