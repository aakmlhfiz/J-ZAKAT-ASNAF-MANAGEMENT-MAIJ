@props(['paginator'])
@if($paginator && $paginator->hasPages())
    @php
        $first = $paginator->firstItem();
        $last  = $paginator->lastItem();
        $total = $paginator->total();
    @endphp
    <div class="px-5 py-4 border-t border-divider flex items-center justify-between flex-wrap gap-3">
        <p class="text-small text-secondary">
            {{ __('pagination.showing', ['first' => $first, 'last' => $last, 'total' => $total]) }}
        </p>
        <div class="flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-[10px]"></i> {{ __('pagination.previous_label') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i> {{ __('pagination.previous_label') }}</a>
            @endif
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm">{{ __('pagination.next_label') }} <i class="fa-solid fa-chevron-right text-[10px]"></i></a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">{{ __('pagination.next_label') }} <i class="fa-solid fa-chevron-right text-[10px]"></i></span>
            @endif
        </div>
    </div>
@endif
