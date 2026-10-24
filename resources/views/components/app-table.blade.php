@props(['hover' => true, 'compact' => false, 'headers' => null, 'footer' => null])
<x-app-card padding="p-0" {{ $attributes }}>
    <div class="overflow-x-auto">
        <table class="app-table {{ $compact ? 'is-compact' : '' }}">
            @if(isset($head) || $headers)
                <thead><tr>{{ $head ?? $headers }}</tr></thead>
            @endif
            <tbody>{{ $body ?? $slot }}</tbody>
        </table>
    </div>
    @if(isset($footer) || $footer)
        {{ $footer }}
    @endif
</x-app-card>
