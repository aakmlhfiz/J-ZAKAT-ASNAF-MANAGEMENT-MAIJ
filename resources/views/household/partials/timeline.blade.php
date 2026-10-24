<x-section-header title="Garis Masa Isi Rumah" icon="fa-timeline" description="Perjalanan bantuan isi rumah dari masa ke masa" />

@php
    $allHistory = $household->assistanceHistories()
        ->with('member')
        ->orderBy('approved_date')
        ->get();

    $timelineItems = [];
    foreach ($allHistory as $item) {
        $year = $item->approved_date?->format('Y') ?? 'Tiada Tahun';
        $timelineItems[$year][] = $item;
    }
    ksort($timelineItems);
@endphp

@if(empty($timelineItems))
    <x-empty-state icon="fa-timeline" title="Tiada Garis Masa" description="Isi rumah ini belum mempunyai sejarah bantuan." />
@else
    <div class="relative">
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary via-primary/40 to-transparent"></div>
        <div class="space-y-0">
            @foreach($timelineItems as $year => $items)
                <div class="relative pl-16 pb-8 group">
                    <div class="absolute left-2 top-0.5 w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center shadow-sm z-10 ring-4 ring-card">
                        <span class="text-[10px] font-bold">{{ substr($year, 2) }}</span>
                    </div>
                    <div class="surface p-4 shadow-xs">
                        <p class="text-small font-bold text-text mb-3">{{ $year }}</p>
                        <div class="space-y-2.5">
                            @foreach($items as $item)
                                <div class="flex items-start gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $item->status === 'approved' ? 'bg-success' : ($item->status === 'rejected' ? 'bg-danger' : 'bg-warning') }} mt-1.5 shrink-0"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] text-text">
                                            <span class="font-semibold text-primary">{{ $item->member?->nama ?? 'N/A' }}</span>
                                            <span class="text-secondary"> — {{ $item->jenis_bantuan }}</span>
                                        </p>
                                        <p class="text-[11px] text-secondary mt-0.5">
                                            RM {{ number_format($item->amount, 2) }}{{ $item->frequency === 'monthly' ? '/bln' : '' }}
                                            @if($item->description) <span class="text-tertiary">· {{ $item->description }}</span> @endif
                                        </p>
                                        <div class="mt-1">
                                            <x-status-badge :status="$item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning')" size="sm" :dot="true">
                                                {{ $item->status === 'approved' ? 'Lulus' : ($item->status === 'rejected' ? 'Tolak' : 'Pending') }}
                                            </x-status-badge>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
