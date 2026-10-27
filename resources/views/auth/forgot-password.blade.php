@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', __('auth.forgot.title').' — J-ZAKAT')

@section('content')
    <div class="auth-card__head auth-card__head--center">
        <span class="auth-badge">
            <x-icon name="key" :size="12" />
            {{ __('auth.forgot.badge') }}
        </span>
        <h1 class="auth-title">{{ __('auth.forgot.title') }}</h1>
        <p class="auth-subtitle">{{ __('auth.forgot.subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="auth-success mb-4">
            <x-icon name="circle-check" :size="17" class="mt-0.5 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST"
          action="{{ $mode === 'portal' ? route('user.password.email') : route('password.email') }}"
          class="auth-form"
          x-data="{ submitting: false }"
          @submit="submitting = true">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">{{ __('auth.fields.email') }}</label>
            <div class="auth-input-wrap">
                <x-icon name="mail" :size="18" class="auth-input-icon" />
                <input type="email" name="email" id="email" required
                       placeholder="{{ __('auth.fields.email_placeholder') }}" autocomplete="email" autofocus
                       value="{{ old('email') }}"
                       @class(['auth-input', 'auth-input--icon', 'is-invalid' => $errors->has('email')])>
            </div>
            @error('email')
                <p class="auth-error" role="alert">
                    <x-icon name="circle-alert" :size="13" />
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="auth-submit" :disabled="submitting">
            <span x-show="!submitting">{{ __('auth.forgot.submit') }}</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <span class="auth-spinner"></span>
                {{ __('auth.forgot.processing') }}
            </span>
            <x-icon name="arrow-right" :size="17" x-show="!submitting" />
        </button>
    </form>
@endsection

@section('footer')
    <a href="{{ $mode === 'portal' ? route('user.login') : route('login') }}" class="auth-back-link">
        <x-icon name="arrow-left" :size="14" />
        {{ __('auth.forgot.back') }}
    </a>
@endsection
