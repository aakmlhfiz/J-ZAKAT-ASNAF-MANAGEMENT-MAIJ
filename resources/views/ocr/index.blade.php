@extends('layouts.app', [
    'title' => __('document_ocr.admin.title'),
    'subtitle' => __('document_ocr.admin.subtitle'),
])

@section('content')
    <x-page-header
        :title="__('document_ocr.admin.title')"
        :subtitle="__('document_ocr.admin.subtitle')"
        icon="scan-line"
    />

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
        @foreach([
            ['label' => __('document_ocr.admin.total'), 'value' => $stats['total'], 'tone' => 'text-primary', 'bg' => 'bg-primary-soft'],
            ['label' => __('document_ocr.admin.uploaded'), 'value' => $stats['uploaded'], 'tone' => 'text-secondary', 'bg' => 'bg-muted'],
            ['label' => __('document_ocr.admin.processing'), 'value' => $stats['processing'], 'tone' => 'text-warning', 'bg' => 'bg-warning-soft'],
            ['label' => __('document_ocr.admin.awaiting_verification'), 'value' => $stats['awaiting_verification'], 'tone' => 'text-warning', 'bg' => 'bg-warning-soft'],
            ['label' => __('document_ocr.admin.verified'), 'value' => $stats['verified'], 'tone' => 'text-success', 'bg' => 'bg-success-soft'],
            ['label' => __('document_ocr.admin.failed'), 'value' => $stats['failed'], 'tone' => 'text-danger', 'bg' => 'bg-danger-soft'],
        ] as $stat)
            <div class="surface p-4">
                <p class="text-eyebrow text-tertiary uppercase tracking-[0.06em]">{{ $stat['label'] }}</p>
                <p class="text-metric-sm mt-2 tabular-nums {{ $stat['tone'] }}">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    <x-app-card class="p-4 mb-4" :hover="false">
        <form method="GET" action="{{ route('ocr.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-small font-medium text-text mb-1">{{ __('document_ocr.admin.search') }}</label>
                <input type="search" name="search" value="{{ request('search') }}" class="input-base w-full">
            </div>
            <div>
                <label class="block text-small font-medium text-text mb-1">{{ __('document_ocr.admin.filter_status') }}</label>
                <select name="status" class="input-base w-full">
                    <option value="">{{ __('document_ocr.admin.all_statuses') }}</option>
                    @foreach(['uploaded','processing','extracted','needs_review','committed','failed','rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('document_ocr.status.'.$status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-small font-medium text-text mb-1">{{ __('document_ocr.admin.filter_type') }}</label>
                <select name="type" class="input-base w-full">
                    <option value="">{{ __('document_ocr.admin.all_types') }}</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass text-[11px]"></i> {{ __('common.search') ?? 'Search' }}</button>
                <a href="{{ route('ocr.index') }}" class="btn btn-outline">{{ __('common.reset') ?? 'Reset' }}</a>
            </div>
        </form>
    </x-app-card>

    <x-app-card padding="p-0" :hover="false">
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>{{ __('document_ocr.admin.file') }}</th>
                        <th>{{ __('document_ocr.admin.type') }}</th>
                        <th>{{ __('document_ocr.admin.applicant') ?? 'Applicant' }}</th>
                        <th>{{ __('document_ocr.admin.status') }}</th>
                        <th class="text-right">{{ __('document_ocr.admin.confidence') }}</th>
                        <th>{{ __('document_ocr.admin.date') }}</th>
                        <th class="text-right">{{ __('document_ocr.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <p class="text-small font-medium text-text max-w-[260px] truncate">{{ $item->original_filename }}</p>
                                <p class="text-[11px] text-tertiary mt-0.5">{{ $item->maskedIc() ?? '—' }}</p>
                            </td>
                            <td class="text-small text-text">{{ $item->type()->label() }}</td>
                            <td class="text-small text-text">{{ $item->portalUser?->name ?? $item->user?->name ?? '—' }}</td>
                            <td><x-status-badge :status="$item->statusTone()">{{ $item->statusLabel() }}</x-status-badge></td>
                            <td class="text-right text-small text-text tabular-nums">
                                {{ $item->overall_confidence !== null ? number_format((float) $item->overall_confidence, 1).'%' : __('document_ocr.review.not_available') }}
                            </td>
                            <td class="text-small text-secondary">{{ $item->created_at?->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                            <td class="text-right">
                                @if($item->type()->isBorang())
                                    <a href="{{ route('import-pdf.review', $item->id) }}" class="btn btn-sm btn-outline">{{ __('document_ocr.admin.review') }}</a>
                                @else
                                    <a href="{{ route('ocr.show', $item->id) }}" class="btn btn-sm btn-outline">{{ __('document_ocr.admin.review') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-small text-secondary py-10">{{ __('document_ocr.admin.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-divider">{{ $items->links() }}</div>
        @endif
    </x-app-card>
@endsection
