@extends('layouts.app', [
    'title' => __('imports.history.meta_title'),
    'subtitle' => __('imports.history.meta_subtitle'),
])
@section('content')
@php
    $current = request('status');
    $search  = request('search');
    $filters = [
        ''           => ['label' => __('imports.history.filter_all'),       'tone' => 'primary', 'count' => $totals['all']        ?? null],
        'extracted'  => ['label' => __('imports.status.extracted'),   'tone' => 'success', 'count' => $totals['extracted']  ?? null],
        'needs_review' => ['label' => __('imports.status.needs_review'), 'tone' => 'warning', 'count' => null],
        'committed'  => ['label' => __('imports.status.committed'),    'tone' => 'success', 'count' => $totals['committed']  ?? null],
        'failed'     => ['label' => __('imports.status.failed'),       'tone' => 'danger',  'count' => $totals['failed']     ?? null],
        'processing' => ['label' => __('imports.status.processing'),   'tone' => 'warning', 'count' => $totals['processing'] ?? null],
    ];
@endphp

<x-page-header :title="__('imports.history.title')" :subtitle="__('imports.history.subtitle', ['count' => $items->total()])" :eyebrow="__('imports.history.eyebrow')" icon="fa-clock-rotate-left">
    <x-slot:actions>
        <x-app-button variant="primary" size="md" icon="fa-plus" href="{{ route('import-pdf.index') }}">{{ __('imports.history.new_import') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

<x-app-card class="p-5 mb-5" :hover="false">
    <form method="GET" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('imports.history.search_placeholder') }}" class="input-base pl-10 py-2.5 text-[13px]">
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
                    <th>{{ __('imports.history.col_file') }}</th>
                    <th>{{ __('imports.history.col_status') }}</th>
                    <th class="text-right">{{ __('imports.history.col_confidence') }}</th>
                    <th>{{ __('imports.history.col_engine') }}</th>
                    <th>{{ __('imports.history.col_pages') }}</th>
                    <th>{{ __('imports.history.col_household') }}</th>
                    <th>{{ __('imports.history.col_profile') }}</th>
                    <th>{{ __('imports.history.col_uploaded_by') }}</th>
                    <th>{{ __('imports.history.col_date') }}</th>
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
                                    'extracted' => __('imports.status.extracted'),
                                    'needs_review' => __('imports.status.needs_review'),
                                    'committed' => __('imports.status.committed'),
                                    'failed' => __('imports.status.failed'),
                                    'processing' => __('imports.status.processing'),
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
                        <td class="text-secondary whitespace-nowrap">{{ $item->created_at->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                        <td>
                            <div class="flex items-center gap-1">
                                @if(in_array($item->status, ['extracted', 'needs_review']))
                                    <a href="{{ route('import-pdf.preview', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="{{ __('imports.history.action_preview') }}">
                                        <i class="fa-solid fa-eye text-[13px]"></i>
                                    </a>
                                @endif
                                <a href="{{ route('import-pdf.show', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="{{ __('imports.history.action_details') }}">
                                    <i class="fa-solid fa-circle-info text-[13px]"></i>
                                </a>
                                <a href="{{ route('import-pdf.file', $item->id) }}" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-primary transition-fast" title="{{ __('imports.history.action_download') }}">
                                    <i class="fa-solid fa-download text-[12px]"></i>
                                </a>
                                <form method="POST" action="{{ route('import-pdf.destroy', $item->id) }}" onsubmit="return confirm(@js(__('imports.history.confirm_delete')))" class="inline-flex">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-danger transition-fast" title="{{ __('imports.history.action_delete') }}">
                                        <i class="fa-solid fa-trash text-[12px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">
                        <x-empty-state icon="fa-file-pdf" :title="__('imports.history.empty_title')" :description="($search || $current) ? __('imports.history.empty_description_filtered') : __('imports.history.empty_description')">
                            <x-slot:action>
                                <x-app-button variant="primary" size="md" icon="fa-upload" href="{{ route('import-pdf.index') }}">{{ __('imports.history.upload_form') }}</x-app-button>
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
