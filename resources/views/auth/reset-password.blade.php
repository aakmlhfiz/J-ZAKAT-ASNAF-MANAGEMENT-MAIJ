@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', __('auth.reset.title').' — J-ZAKAT')

@section('content')
    <div class="auth-card__head auth-card__head--center">
        <span class="auth-badge">
            <x-icon name="shield-check" :size="12" />
            {{ __('auth.reset.badge') }}
        </span>
        <h1 class="auth-title">{{ __('auth.reset.title') }}</h1>
        <p class="auth-subtitle">{{ __('auth.reset.subtitle') }}</p>
    </div>

    <form method="POST"
          action="{{ $mode === 'portal' ? route('user.password.update') : route('password.update') }}"
          class="auth-form"
          x-data="{ showPassword: false, showPasswordConfirmation: false, submitting: false }"
          @submit="submitting = true">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="auth-field">
            <label for="password" class="auth-label">{{ __('auth.reset.password') }}</label>
            <div class="auth-input-wrap">
                <x-icon name="lock" :size="18" class="auth-input-icon" />
                <input type="password" name="password" id="password" required
                       placeholder="{{ __('auth.fields.password_new_placeholder') }}" autocomplete="new-password" autofocus
                       x-bind:type="showPassword ? 'text' : 'password'"
                       @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                <button type="button" class="auth-eye"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                    <span x-show="!showPassword"><x-icon name="eye" :size="18" /></span>
                    <span x-show="showPassword" x-cloak><x-icon name="eye-off" :size="18" /></span>
                </button>
            </div>
            @error('password')
                <p class="auth-error" role="alert">
                    <x-icon name="circle-alert" :size="13" />
                    {{ $message }}
                </p>
            @else
                <p class="auth-hint">{{ __('auth.fields.password_hint') }}</p>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation" class="auth-label">{{ __('auth.reset.confirm') }}</label>
            <div class="auth-input-wrap">
                <x-icon name="lock" :size="18" class="auth-input-icon" />
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       placeholder="{{ __('auth.fields.confirm_placeholder') }}" autocomplete="new-password"
                       x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                       @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                <button type="button" class="auth-eye"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        :aria-label="showPasswordConfirmation ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                    <span x-show="!showPasswordConfirmation"><x-icon name="eye" :size="18" /></span>
                    <span x-show="showPasswordConfirmation" x-cloak><x-icon name="eye-off" :size="18" /></span>
                </button>
            </div>
            @error('password_confirmation')
                <p class="auth-error" role="alert">
                    <x-icon name="circle-alert" :size="13" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="auth-submit" :disabled="submitting">
            <span x-show="!submitting">{{ __('auth.reset.submit') }}</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <span class="auth-spinner"></span>
                {{ __('auth.reset.processing') }}
            </span>
            <x-icon name="arrow-right" :size="17" x-show="!submitting" />
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ $mode === 'portal' ? route('user.login') : route('login') }}" class="auth-back-link">
        <x-icon name="arrow-left" :size="14" />
        {{ __('auth.reset.back') }}
    </a>
@endsection
