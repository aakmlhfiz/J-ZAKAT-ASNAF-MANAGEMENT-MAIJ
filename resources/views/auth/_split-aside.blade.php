@php
    $isPortal = ($context ?? 'admin') === 'portal';
    $mode = $isPortal ? 'portal' : 'admin';
@endphp

<aside class="auth-visual" aria-hidden="true">
    <div class="auth-visual__inner">
        <a href="{{ url('/') }}" class="auth-visual__brand" tabindex="-1">
            <span class="auth-logo">
                <img src="{{ asset('images/MAIJ.PNG') }}" alt="">
            </span>
            <span class="auth-brand-copy">
                <span class="auth-wordmark">J-ZAKAT</span>
                <span class="auth-brand-sub">{{ __('auth.brand.tagline') }}</span>
            </span>
        </a>

        <div class="auth-visual__content">
            <span class="auth-eyebrow">
                <x-icon name="landmark" :size="13" />
                {{ __('auth.roles.'.$mode) }} &middot; {{ __('auth.system_official') }}
            </span>

            <h1>
                {{ __('auth.aside.'.$mode.'.headline') }}
                <span class="auth-highlight">{{ __('auth.aside.highlight') }}</span>
            </h1>

            <p class="auth-visual__lede">
                {{ __('auth.aside.lede') }}
            </p>

            <ul class="auth-points">
                <li>
                    <span class="auth-point-icon"><x-icon name="shield-check" :size="18" :stroke="1.8" /></span>
                    <span>
                        <strong>{{ __('auth.aside.points.secure.title') }}</strong>
                        <span>{{ __('auth.aside.points.secure.desc') }}</span>
                    </span>
                </li>
                <li>
                    <span class="auth-point-icon"><x-icon name="scale" :size="18" :stroke="1.8" /></span>
                    <span>
                        <strong>{{ __('auth.aside.points.assessment.title') }}</strong>
                        <span>{{ __('auth.aside.points.assessment.desc') }}</span>
                    </span>
                </li>
                <li>
                    <span class="auth-point-icon"><x-icon name="clipboard-check" :size="18" :stroke="1.8" /></span>
                    <span>
                        <strong>{{ __('auth.aside.points.audit.title') }}</strong>
                        <span>{{ __('auth.aside.points.audit.desc') }}</span>
                    </span>
                </li>
            </ul>
        </div>

        <div class="auth-visual__footer">
            <span class="auth-pulse" aria-hidden="true"></span>
            {{ __('auth.aside.footer') }}
        </div>
    </div>
</aside>
