@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', __('auth.two_factor.title').' — J-ZAKAT')

@section('content')
    <div class="auth-card__head auth-card__head--center">
        <span class="auth-badge">
            <x-icon name="shield-check" :size="12" />
            {{ __('auth.two_factor.badge') }}
        </span>
        <h1 class="auth-title">{{ __('auth.two_factor.title') }}</h1>
        <p class="auth-subtitle">{{ __('auth.two_factor.subtitle') }}</p>
    </div>

    <form method="POST"
          action="{{ $mode === 'portal' ? route('user.2fa.verify') : route('auth.2fa.verify') }}"
          class="auth-form"
          x-data="{ submitting: false }"
          @submit="submitting = true">
        @csrf

        <div class="auth-field">
            <label for="code" class="auth-label">{{ __('auth.two_factor.code') }}</label>
            <input type="text" name="code" id="code"
                   class="auth-input auth-code @error('code') is-invalid @enderror"
                   maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                   placeholder="000000" autofocus required>
            @error('code')
                <p class="auth-error" role="alert">
                    <x-icon name="circle-alert" :size="13" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="auth-submit" :disabled="submitting">
            <span x-show="!submitting">{{ __('auth.two_factor.submit') }}</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <span class="auth-spinner"></span>
                {{ __('auth.two_factor.processing') }}
            </span>
            <x-icon name="arrow-right" :size="17" x-show="!submitting" />
        </button>
    </form>
@endsection

@section('footer')
    <form method="POST" action="{{ $mode === 'portal' ? route('user.2fa.cancel') : route('auth.2fa.cancel') }}">
        @csrf
        <button type="submit" class="auth-back-link" style="background: none; border: 0; cursor: pointer;">
            <x-icon name="arrow-left" :size="14" />
            {{ __('auth.two_factor.back') }}
        </button>
    </form>
@endsection
