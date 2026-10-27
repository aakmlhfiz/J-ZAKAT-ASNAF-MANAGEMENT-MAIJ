{{-- Admin registration — modern split auth matching /login --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="{{ __('auth.register.admin.title') }} — J-ZAKAT">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.register.admin.title') }} — J-ZAKAT</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
<div class="auth-shell"
     x-data="{
        showPassword: false,
        showPasswordConfirmation: false,
        submitting: false
     }">

    @include('auth._split-aside', ['context' => 'admin'])

    <main class="auth-panel">
        @include('auth._auth-topbar')

        <div class="auth-panel__content">
            <div class="auth-card auth-card--wide">
                <div class="auth-card__head">
                    <span class="auth-badge">
                        <x-icon name="user-plus" :size="12" />
                        {{ __('auth.register.admin.badge') }}
                    </span>
                    <h1 class="auth-title">{{ __('auth.register.admin.title') }}</h1>
                    <p class="auth-subtitle">{{ __('auth.register.admin.subtitle') }}</p>
                </div>

                <div class="auth-tabs" aria-label="{{ __('auth.tabs.login') }} / {{ __('auth.tabs.register') }}">
                    <a href="{{ route('login') }}" class="auth-tab">{{ __('auth.tabs.login') }}</a>
                    <a href="{{ route('register') }}" class="auth-tab is-active">{{ __('auth.tabs.register') }}</a>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate
                      @submit="submitting = true">
                    @csrf

                    <div class="auth-field">
                        <label for="name" class="auth-label">{{ __('auth.fields.name') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="user" :size="18" class="auth-input-icon" />
                            <input id="name" type="text" name="name" required maxlength="255"
                                   autocomplete="name" placeholder="{{ __('auth.fields.name_placeholder') }}"
                                   value="{{ old('name') }}"
                                   @class(['auth-input', 'auth-input--icon', 'is-invalid' => $errors->has('name')])>
                        </div>
                        @if($errors->has('name'))
                            <p class="auth-error" role="alert">
                                <x-icon name="circle-alert" :size="13" />
                                {{ $errors->first('name') }}
                            </p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="email" class="auth-label">{{ __('auth.fields.email') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="mail" :size="18" class="auth-input-icon" />
                            <input id="email" type="email" name="email" required maxlength="255"
                                   autocomplete="email" placeholder="{{ __('auth.fields.email_placeholder') }}"
                                   value="{{ old('email') }}"
                                   @class(['auth-input', 'auth-input--icon', 'is-invalid' => $errors->has('email')])>
                        </div>
                        @if($errors->has('email'))
                            <p class="auth-error" role="alert">
                                <x-icon name="circle-alert" :size="13" />
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="invitation_code" class="auth-label">{{ __('auth.fields.invitation') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="key" :size="18" class="auth-input-icon" />
                            <input id="invitation_code" type="text" name="invitation_code" required maxlength="64"
                                   placeholder="{{ __('auth.fields.invitation_placeholder') }}"
                                   value="{{ old('invitation_code') }}"
                                   @class(['auth-input', 'auth-input--icon', 'is-invalid' => $errors->has('invitation_code')])>
                        </div>
                        @if($errors->has('invitation_code'))
                            <p class="auth-error" role="alert">
                                <x-icon name="circle-alert" :size="13" />
                                {{ $errors->first('invitation_code') }}
                            </p>
                        @else
                            <p class="auth-hint">
                                <x-icon name="lock" :size="12" />
                                {{ __('auth.fields.invitation_hint') }}
                            </p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('auth.fields.password') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" :size="18" class="auth-input-icon" />
                            <input id="password" type="password" name="password" required minlength="8"
                                   placeholder="{{ __('auth.fields.password_new_placeholder') }}" autocomplete="new-password"
                                   x-bind:type="showPassword ? 'text' : 'password'"
                                   @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                            <button type="button" class="auth-eye"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                <span x-show="!showPassword"><x-icon name="eye" :size="18" /></span>
                                <span x-show="showPassword" x-cloak><x-icon name="eye-off" :size="18" /></span>
                            </button>
                        </div>
                        @if($errors->has('password'))
                            <p class="auth-error" role="alert">
                                <x-icon name="circle-alert" :size="13" />
                                {{ $errors->first('password') }}
                            </p>
                        @else
                            <p class="auth-hint">{{ __('auth.fields.password_hint') }}</p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation" class="auth-label">{{ __('auth.fields.confirm_password') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" :size="18" class="auth-input-icon" />
                            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
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
                        @if($errors->has('password_confirmation'))
                            <p class="auth-error" role="alert">
                                <x-icon name="circle-alert" :size="13" />
                                {{ $errors->first('password_confirmation') }}
                            </p>
                        @endif
                    </div>

                    <label class="auth-check auth-check--top">
                        <input type="checkbox" required>
                        <span>{{ __('auth.terms') }}</span>
                    </label>

                    <button type="submit" class="auth-submit" :disabled="submitting">
                        <span x-show="!submitting">{{ __('auth.register.admin.submit') }}</span>
                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                            <span class="auth-spinner"></span>
                            {{ __('auth.login.processing') }}
                        </span>
                        <x-icon name="arrow-right" :size="17" x-show="!submitting" />
                    </button>

                    <p class="auth-switch">
                        {{ __('auth.register.admin.has_account') }}
                        <a href="{{ route('login') }}">{{ __('auth.register.admin.login_link') }}</a>
                    </p>
                </form>
            </div>

            @include('auth._auth-footer')
        </div>
    </main>
</div>
</body>
</html>
