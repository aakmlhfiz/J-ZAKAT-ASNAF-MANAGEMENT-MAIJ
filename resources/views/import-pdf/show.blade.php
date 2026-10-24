@extends('layouts.app', [
    'title' => 'Butiran Import PDF',
    'subtitle' => $import->original_filename,
])

@php
    $extracted = $extracted ?? [];
    $confidence = $import->overall_confidence ?? 0;
@endphp

@section('content')

<x-page-header title="Butiran Import PDF" subtitle="{{ $import->original_filename }}" eyebrow="Pemprosesan Dokumen AI" icon="fa-circle-info">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">Lihat Fail</x-app-button>
        @if(in_array($import->status, ['extracted', 'needs_review']))
            <x-app-button variant="primary" size="md" icon="fa-eye" href="{{ route('import-pdf.preview', $import->id) }}">Pratinjau</x-app-button>
        @endif
        <x-app-button variant="outline" size="md" icon="fa-arrow-left" href="{{ route('import-pdf.history') }}">Sejarah</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header title="Maklumat Import" icon="fa-circle-info" />
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-eyebrow text-tertiary">Nama Fail</dt>
                    <dd class="text-small text-text font-medium">{{ $import->original_filename }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Status</dt>
                    <dd class="text-small">
                        @php
                            $statusColor = match($import->status) {
                                'extracted' => 'success',
                                'needs_review' => 'warning',
                                'committed' => 'success',
                                'failed' => 'danger',
                                'processing' => 'warning',
                                default => 'warning',
                            };
                        @endphp
                        <x-status-badge :status="$statusColor">{{ $import->status }}</x-status-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Jenis Fail</dt>
                    <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Saiz</dt>
                    <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Halaman</dt>
                    <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Hash</dt>
                    <dd class="text-small text-text font-mono text-[11px] truncate">{{ $import->file_hash }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Enjin OCR</dt>
                    <dd class="text-small text-text">{{ $import->ocr_engine ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Keyakinan AI</dt>
                    <dd class="text-small text-text font-semibold">{{ $confidence }}%</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Dimuat Naik</dt>
                    <dd class="text-small text-text">{{ $import->created_at->format('d M Y, h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Diproses</dt>
                    <dd class="text-small text-text">{{ $import->ocr_completed_at ? $import->ocr_completed_at->format('d M Y, h:i A') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Diupload Oleh</dt>
                    <dd class="text-small text-text">{{ $import->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">Profil Asnaf</dt>
                    <dd class="text-small text-text">
                        @if($import->profil_asnaf_ic)
                            <a href="{{ route('viewprofile', $import->profil_asnaf_ic) }}" class="text-primary hover:underline">{{ $import->profil_asnaf_ic }}</a>
                        @else
                            <span class="text-tertiary">—</span>
                        @endif
                    </dd>
                </div>
                @if($import->household_id)
                    <div>
                        <dt class="text-eyebrow text-tertiary">Isi Rumah</dt>
                        <dd class="text-small text-text">
                            <a href="{{ route('household.show', $import->household_id) }}" class="text-primary hover:underline">{{ $import->household?->household_id ?? 'HR-' . $import->household_id }}</a>
                        </dd>
                    </div>
                @endif
            </dl>

            @if($import->error_message)
                <div class="mt-4 p-4 rounded-[12px] bg-danger-soft border border-danger/20">
                    <p class="text-small font-semibold text-danger">Ralat</p>
                    <p class="text-small text-text mt-1">{{ $import->error_message }}</p>
                </div>
            @endif
        </x-app-card>

        @if(!empty($extracted))
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Data Diekstrak ({{ count($extracted) }} medan)" icon="fa-database" />
                <div class="divide-y divide-divider">
                    @foreach($extracted as $field => $data)
                        @php
                            $value = $data['value'] ?? '—';
                            $conf = (int) ($data['confidence'] ?? 0);
                            $label = ucwords(str_replace('_', ' ', $field));
                        @endphp
                        <div class="flex items-center justify-between py-2.5 gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-eyebrow text-tertiary">{{ $label }}</p>
                                <p class="text-small text-text font-medium truncate">{{ is_scalar($value) ? (string) $value : json_encode($value) }}</p>
                            </div>
                            <div class="shrink-0 w-[120px]">
                                <x-import-pdf.confidence-meter :confidence="$conf" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-app-card>
        @endif

        @php $hm = $import->household_members_extracted ?? []; @endphp
        @if(!empty($hm))
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Ahli Isi Rumah Dikesan" icon="fa-people-group" />
                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>IC</th>
                                <th>Hubungan</th>
                                <th>Umur</th>
                                <th>Pekerjaan</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hm as $m)
                                <tr>
                                    <td class="text-small text-text font-medium">{{ $m['nama'] ?? '—' }}</td>
                                    <td class="text-small text-text tabular-nums">{{ $m['ic'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $m['hubungan'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $m['umur'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $m['pekerjaan'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ isset($m['pendapatan_bulanan']) ? 'RM' . number_format((float) $m['pendapatan_bulanan'], 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-app-card>
        @endif
    </div>

    <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
        <x-app-card class="p-6" :hover="false" elevated accent="primary">
            <x-section-header title="Keyakinan AI" icon="fa-bullseye" />
            <div class="text-center py-3">
                <p class="text-[42px] font-extrabold text-text tracking-tight tabular-nums">{{ $confidence }}<span class="text-tertiary text-h3">%</span></p>
            </div>
            <div class="h-3 bg-muted rounded-pill overflow-hidden mt-3">
                <div class="h-full rounded-pill transition-all duration-500 @if($confidence >= 90) bg-success @elseif($confidence >= 70) bg-warning @else bg-danger @endif" style="width: {{ max(3, $confidence) }}%"></div>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header title="Label Dikesan" icon="fa-tags" />
            @php $matchedLabels = $import->matched_labels ?? []; @endphp
            @if(empty($matchedLabels))
                <p class="text-small text-secondary">Tiada label dikesan.</p>
            @else
                <ul class="space-y-1.5">
                    @foreach($matchedLabels as $match)
                        <li class="flex items-center justify-between text-[12px] py-1.5 px-2.5 rounded-[8px] bg-muted">
                            <span class="text-text font-medium">"{{ $match['label'] ?? $match }}"</span>
                            <span class="text-tertiary">→ {{ $match['field'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-app-card>

        @if($import->duplicate_warnings)
            <x-app-card class="p-6" :hover="false" elevated accent="warning">
                <x-section-header title="Amaran Pendua" icon="fa-triangle-exclamation" />
                @php $dw = $import->duplicate_warnings; @endphp
                <div class="space-y-2">
                    @foreach(($dw['warnings'] ?? []) as $warning)
                        <div class="p-2.5 rounded-[10px] @if(($warning['severity'] ?? '') === 'high') bg-danger-soft border border-danger/20 @else bg-warning-soft/30 border border-warning/15 @endif">
                            <p class="text-[12px] font-semibold text-text">{{ $warning['title'] ?? '' }}</p>
                            <p class="text-[11px] text-secondary mt-0.5">{{ $warning['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-app-card>
        @endif

        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header title="Tindakan" icon="fa-gear" />
            <div class="space-y-2">
                @if(in_array($import->status, ['extracted', 'needs_review']))
                    <x-app-button variant="primary" size="sm" block icon="fa-check-circle" href="{{ route('import-pdf.preview', $import->id) }}">Semak & Simpan</x-app-button>
                @endif
                <x-app-button variant="outline" size="sm" block icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">Muat Turun Fail Asal</x-app-button>
                <form method="POST" action="{{ route('import-pdf.destroy', $import->id) }}" onsubmit="return confirm('Padam rekod import ini?')">
                    @csrf
                    @method('DELETE')
                    <x-app-button type="submit" variant="ghost" size="sm" block icon="fa-trash" class="text-danger">Padam Rekod</x-app-button>
                </form>
            </div>
        </x-app-card>
    </div>
</div>
@endsection
