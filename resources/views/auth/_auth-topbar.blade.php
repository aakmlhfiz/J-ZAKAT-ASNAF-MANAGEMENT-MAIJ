<header class="auth-panel__top">
    <a href="{{ url('/') }}" class="auth-brand-mobile" aria-label="{{ __('auth.back_home') }}">
        <span class="auth-logo">
            <img src="{{ asset('images/MAIJ.PNG') }}" alt="">
        </span>
        <span class="auth-brand-copy">
            <span class="auth-wordmark">J-ZAKAT</span>
            <span class="auth-brand-sub">{{ __('auth.brand.tagline') }}</span>
        </span>
    </a>

    <div class="flex items-center gap-2 sm:gap-3">
        <x-language-switcher variant="dark" />

        <a href="{{ url('/') }}" class="auth-back">
            <x-icon name="arrow-left" :size="15" />
            <span class="hidden sm:inline">{{ __('auth.back_home') }}</span>
        </a>
    </div>
</header>
