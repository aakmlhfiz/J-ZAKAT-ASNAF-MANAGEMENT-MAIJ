@props(['paginator'])
@if($paginator && $paginator->hasPages())
    @php
        $first = $paginator->firstItem();
        $last  = $paginator->lastItem();
        $total = $paginator->total();
    @endphp
    <div class="px-5 py-4 border-t border-divider flex items-center justify-between flex-wrap gap-3">
        <p class="text-small text-secondary">
            Memaparkan <span class="font-semibold text-text">{{ $first }}</span>–<span class="font-semibold text-text">{{ $last }}</span> daripada <span class="font-semibold text-text">{{ $total }}</span> rekod
        </p>
        <div class="flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelum</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelum</a>
            @endif
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm">Seterusnya <i class="fa-solid fa-chevron-right text-[10px]"></i></a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">Seterusnya <i class="fa-solid fa-chevron-right text-[10px]"></i></span>
            @endif
        </div>
    </div>
@endif
