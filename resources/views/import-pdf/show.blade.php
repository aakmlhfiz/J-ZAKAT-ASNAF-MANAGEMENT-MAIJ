@extends('layouts.app', [
    'title' => __('imports.show.meta_title'),
    'subtitle' => $import->original_filename,
])

@php
    $extracted = $extracted ?? [];
    $confidence = $import->overall_confidence ?? 0;
@endphp

@section('content')

<x-page-header :title="__('imports.show.title')" :subtitle="$import->original_filename" :eyebrow="__('imports.show.eyebrow')" icon="fa-circle-info">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">{{ __('imports.show.view_file') }}</x-app-button>
        @if(in_array($import->status, ['extracted', 'needs_review']))
            <x-app-button variant="primary" size="md" icon="fa-eye" href="{{ route('import-pdf.preview', $import->id) }}">{{ __('imports.show.preview') }}</x-app-button>
        @endif
        <x-app-button variant="outline" size="md" icon="fa-arrow-left" href="{{ route('import-pdf.history') }}">{{ __('imports.show.history') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-app-card class="p-6" :hover="false">
            <x-section-header :title="__('imports.show.import_info_title')" icon="fa-circle-info" />
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.file_name') }}</dt>
                    <dd class="text-small text-text font-medium">{{ $import->original_filename }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.status') }}</dt>
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
                            $statusLabel = match($import->status) {
                                'extracted' => __('imports.status.extracted'),
                                'needs_review' => __('imports.status.needs_review'),
                                'committed' => __('imports.status.committed'),
                                'failed' => __('imports.status.failed'),
                                'processing' => __('imports.status.processing'),
                                default => $import->status,
                            };
                        @endphp
                        <x-status-badge :status="$statusColor">{{ $statusLabel }}</x-status-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.file_type') }}</dt>
                    <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.file_size') }}</dt>
                    <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.file_pages') }}</dt>
                    <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.hash') }}</dt>
                    <dd class="text-small text-text font-mono text-[11px] truncate">{{ $import->file_hash }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.ocr_engine') }}</dt>
                    <dd class="text-small text-text">{{ $import->ocr_engine ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.ai_confidence') }}</dt>
                    <dd class="text-small text-text font-semibold">{{ $confidence }}%</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.uploaded_at') }}</dt>
                    <dd class="text-small text-text">{{ $import->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.processed_at') }}</dt>
                    <dd class="text-small text-text">{{ $import->ocr_completed_at ? $import->ocr_completed_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.uploaded_by') }}</dt>
                    <dd class="text-small text-text">{{ $import->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-eyebrow text-tertiary">{{ __('imports.show.asnaf_profile') }}</dt>
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
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.show.household') }}</dt>
                        <dd class="text-small text-text">
                            <a href="{{ route('household.show', $import->household_id) }}" class="text-primary hover:underline">{{ $import->household?->household_id ?? 'HR-' . $import->household_id }}</a>
                        </dd>
                    </div>
                @endif
            </dl>

            @if($import->error_message)
                <div class="mt-4 p-4 rounded-[12px] bg-danger-soft border border-danger/20">
                    <p class="text-small font-semibold text-danger">{{ __('imports.show.error') }}</p>
                    <p class="text-small text-text mt-1">{{ $import->error_message }}</p>
                </div>
            @endif
        </x-app-card>

        @if(!empty($extracted))
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.show.extracted_data', ['count' => count($extracted)])" icon="fa-database" />
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
                <x-section-header :title="__('imports.show.household_members')" icon="fa-people-group" />
                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>{{ __('imports.show.col_name') }}</th>
                                <th>{{ __('imports.show.col_ic') }}</th>
                                <th>{{ __('imports.show.col_relationship') }}</th>
                                <th>{{ __('imports.show.col_age') }}</th>
                                <th>{{ __('imports.show.col_occupation') }}</th>
                                <th>{{ __('imports.show.col_income') }}</th>
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
            <x-section-header :title="__('imports.show.confidence_title')" icon="fa-bullseye" />
            <div class="text-center py-3">
                <p class="text-[42px] font-extrabold text-text tracking-tight tabular-nums">{{ $confidence }}<span class="text-tertiary text-h3">%</span></p>
            </div>
            <div class="h-3 bg-muted rounded-pill overflow-hidden mt-3">
                <div class="h-full rounded-pill transition-all duration-500 @if($confidence >= 90) bg-success @elseif($confidence >= 70) bg-warning @else bg-danger @endif" style="width: {{ max(3, $confidence) }}%"></div>
            </div>
        </x-app-card>

        <x-app-card class="p-6" :hover="false" elevated>
            <x-section-header :title="__('imports.show.labels_title')" icon="fa-tags" />
            @php $matchedLabels = $import->matched_labels ?? []; @endphp
            @if(empty($matchedLabels))
                <p class="text-small text-secondary">{{ __('imports.show.no_labels') }}</p>
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
                <x-section-header :title="__('imports.show.duplicates_title')" icon="fa-triangle-exclamation" />
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
            <x-section-header :title="__('imports.show.actions_title')" icon="fa-gear" />
            <div class="space-y-2">
                @if(in_array($import->status, ['extracted', 'needs_review']))
                    <x-app-button variant="primary" size="sm" block icon="fa-check-circle" href="{{ route('import-pdf.preview', $import->id) }}">{{ __('imports.show.review_save') }}</x-app-button>
                @endif
                <x-app-button variant="outline" size="sm" block icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">{{ __('imports.show.download_original') }}</x-app-button>
                <form method="POST" action="{{ route('import-pdf.destroy', $import->id) }}" onsubmit="return confirm(@js(__('imports.show.confirm_delete')))">
                    @csrf
                    @method('DELETE')
                    <x-app-button type="submit" variant="ghost" size="sm" block icon="fa-trash" class="text-danger">{{ __('imports.show.delete_record') }}</x-app-button>
                </form>
            </div>
        </x-app-card>
    </div>
</div>
@endsection
