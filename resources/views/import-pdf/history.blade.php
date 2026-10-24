@extends('layouts.app', [
    'title' => 'Sejarah Import PDF',
    'subtitle' => 'Rekod import dokumen & pengekstrakan AI',
])
@section('content')
@php
    $current = request('status');
    $search  = request('search');
    $filters = [
        ''           => ['label' => 'Semua',       'tone' => 'primary', 'count' => $totals['all']        ?? null],
        'extracted'  => ['label' => 'Diekstrak',   'tone' => 'success', 'count' => $totals['extracted']  ?? null],
        'needs_review' => ['label' => 'Perlu Semakan', 'tone' => 'warning', 'count' => null],
        'committed'  => ['label' => 'Disimpan',    'tone' => 'success', 'count' => $totals['committed']  ?? null],
        'failed'     => ['label' => 'Gagal',       'tone' => 'danger',  'count' => $totals['failed']     ?? null],
        'processing' => ['label' => 'Memproses',   'tone' => 'warning', 'count' => $totals['processing'] ?? null],
    ];
@endphp

<x-page-header title="Sejarah Import PDF" subtitle="{{ $items->total() }} rekod dijumpai" eyebrow="Pemprosesan Dokumen AI" icon="fa-clock-rotate-left">
    <x-slot:actions>
        <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('import-pdf.index') }}">Import Baharu</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama fail atau IC..." class="input-base pl-10 py-2.5 text-[13px]">
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($filters as $value => $f)
                @php
                    $isActive = ($current ?? '') === $value;
                    $href = $value === '' ? route('import-pdf.history', ['search' => $search]) : route('import-pdf.history', ['status' => $value, 'search' => $search]);
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
                    @if($f['count'] !== null)<span class="opacity-70 tabular-nums">{{ $f['count'] }}</span>@endif
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
                    <th>Fail</th>
                    <th>Status</th>
                    <th class="text-right">Keyakinan</th>
                    <th>Enjin OCR</th>
                    <th>Halaman</th>
                    <th>Isi Rumah</th>
                    <th>Profil</th>
                    <th>Diupload Oleh</th>
                    <th>Tarikh</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-[10px] bg-primary-soft flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-pdf text-primary text-[13px]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-small text-text font-medium truncate max-w-[220px]" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                    <p class="text-eyebrow text-tertiary">{{ number_format($item->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusColor = match($item->status) {
                                    'extracted' => 'success',
                                    'needs_review' => 'warning',
                                    'committed' => 'success',
                                    'failed' => 'danger',
                                    'processing' => 'warning',
                                    default => 'warning',
                                };
                                $statusLabel = match($item->status) {
                                    'extracted' => 'Diekstrak',
                                    'needs_review' => 'Perlu Semakan',
                                    'committed' => 'Disimpan',
                                    'failed' => 'Gagal',
                                    'processing' => 'Memproses',
                                    default => $item->status,
                                };
                            @endphp
                            <x-status-badge :status="$statusColor">{{ $statusLabel }}</x-status-badge>
                        </td>
                        <td class="text-right tabular-nums">
                            @if($item->overall_confidence !== null)
                                <span class="@if($item->overall_confidence >= 90) text-success @elseif($item->overall_confidence >= 70) text-warning @else text-danger @endif font-semibold">
                                    {{ $item->overall_confidence }}%
                                </span>
                            @else
                                <span class="text-tertiary">—</span>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $item->ocr_engine ?? '—' }}</td>
                        <td class="text-secondary tabular-nums">{{ $item->page_count ?? '—' }}</td>
                        <td>
                            @if($item->household_id)
                                <span class="text-small text-text font-medium">{{ $item->household?->household_id ?? 'HR-' . $item->household_id }}</span>
                            @else
                                <span class="text-tertiary">—</span>
                            @endif
                        </td>
                        <td>
                            @if($item->profil_asnaf_ic)
                                <a href="{{ route('viewprofile', $item->profil_asnaf_ic) }}" class="text-primary hover:underline font-medium tabular-nums">
                                    {{ $item->profil_asnaf_ic }}
                                </a>
                            @else
                                <span class="text-tertiary">—</span>
                            @endif
                        </td>
                        <td class="text-secondary whitespace-nowrap">{{ $item->user?->name ?? '—' }}</td>
                        <td class="text-secondary whitespace-nowrap">{{ $item->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="flex items-center gap-1">
                                @if(in_array($item->status, ['extracted', 'needs_review']))
                                    <a href="{{ route('import-pdf.preview', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="Pratinjau">
                                        <i class="fa-solid fa-eye text-[13px]"></i>
                                    </a>
                                @endif
                                <a href="{{ route('import-pdf.show', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="Butiran">
                                    <i class="fa-solid fa-circle-info text-[13px]"></i>
                                </a>
                                <a href="{{ route('import-pdf.file', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="Muat Turun">
                                    <i class="fa-solid fa-download text-[12px]"></i>
                                </a>
                                <form method="POST" action="{{ route('import-pdf.destroy', $item->id) }}" onsubmit="return confirm('Padam rekod ini?')" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-danger transition-fast" title="Padam">
                                        <i class="fa-solid fa-trash text-[12px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">
                        <x-empty-state icon="fa-file-pdf" title="Tiada rekod dijumpai" :description="($search || $current) ? 'Cuba ubah carian atau penapis anda.' : 'Muat naik dokumen untuk mula.'">
                            <x-slot:action>
                                <x-app-button variant="primary" size="md" icon="fa-upload" href="{{ route('import-pdf.index') }}">Muat Naik Borang</x-app-button>
                            </x-slot:action>
                        </x-empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div class="p-4 border-t border-divider">
            {{ $items->links('components.pagination') }}
        </div>
    @endif
</x-app-card>
@endsection
