@props(['placeholder' => 'Cari...', 'name' => 'search', 'value' => null, 'method' => 'GET', 'action' => null, 'size' => 'md'])
@php $sizeCls = $size === 'sm' ? 'pl-10 py-2 text-[13px]' : 'pl-11 py-3 text-[14px]'; @endphp
<form method="{{ $method }}" action="{{ $action }}" class="relative w-full max-w-sm">
    <x-icon name="search" :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" />
    <input type="search" name="{{ $name }}" value="{{ $value ?? request($name) }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => "input-base {$sizeCls}"])}}>
    @if(isset($filters))<div class="hidden">{{ $filters }}</div>@endif
</form>
