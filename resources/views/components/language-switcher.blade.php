@props(['variant' => 'dark'])

@php
    $current = app()->getLocale();
    $isDark = $variant === 'dark';
    $container = $isDark ? 'border-white/15 bg-white/8' : 'border-divider bg-muted';
    $inactive = $isDark ? 'text-white/65 hover:text-white' : 'text-[#475569] hover:text-text';
    $active = $isDark ? 'bg-cyan text-[#07111F]' : 'bg-primary text-white';
@endphp

<form method="POST" action="{{ route('locale.update') }}"
      aria-label="{{ __('common.language') }}"
      {{ $attributes->merge(['class' => "inline-flex items-center gap-0.5 rounded-[6px] border {$container} p-0.5"]) }}>
    @csrf
    @foreach(['ms' => 'BM', 'en' => 'EN'] as $code => $label)
        <button type="submit"
                name="locale"
                value="{{ $code }}"
                title="{{ __('common.language_'.$code) }}"
                aria-pressed="{{ $current === $code ? 'true' : 'false' }}"
                class="min-w-[32px] rounded-[4px] px-2 py-1 text-[10px] font-semibold leading-none transition-fast {{ $current === $code ? $active : $inactive }}">
            {{ $label }}
        </button>
    @endforeach
</form>
