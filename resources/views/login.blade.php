{{--
    Modern split login (admin + portal).
    Mode handling:
      $mode = 'admin'  -> POST route('login') / route('register')
      $mode = 'portal' -> POST route('user.login') / route('user.register')
--}}
@php
    $mode = $mode ?? 'admin';
    $isPortal = $mode === 'portal';

    $loginAction    = $isPortal ? route('user.login') : route('login');
    $registerAction = $isPortal ? route('user.register') : route('register');
    $forgotUrl      = $isPortal ? route('user.password.request') : route('password.request');
    $otherLoginUrl  = $isPortal ? route('login') : route('user.login');
    $otherLoginText = $isPortal ? __('auth.login.switch_portal_link') : __('auth.login.switch_admin_link');

    $states = [
        'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang',
        'Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor',
        'Terengganu','Kuala Lumpur','Putrajaya','Labuan',
    ];

    $registerErrorBag = $errors->register ?? $errors->userRegister ?? null;
    $hasRegisterOld = $registerErrorBag && $registerErrorBag->isNotEmpty();
    $initialTab = (request('tab') === 'signup' || $hasRegisterOld
        || old('invitation_code') || old('ic_no')) ? 'signup' : 'signin';

    $tabTitle = $initialTab === 'signup'
        ? ($isPortal ? __('auth.meta.portal_register') : __('auth.meta.register'))
        : ($isPortal ? __('auth.meta.portal_login') : __('auth.meta.login'));

    $roleLabel = __('auth.roles.'.$mode);
    $signinTitle = __('auth.login.title');
    $signinSubtitle = $isPortal ? __('auth.login.subtitle_portal') : __('auth.login.subtitle_admin');
    $signupTitle = $isPortal ? __('auth.login.signup_title_portal') : __('auth.login.signup_title_admin');
    $signupSubtitle = $isPortal ? __('auth.login.signup_subtitle_portal') : __('auth.login.signup_subtitle_admin');
    $displayTitle = $initialTab === 'signup' ? $signupTitle : $signinTitle;
    $displaySubtitle = $initialTab === 'signup' ? $signupSubtitle : $signinSubtitle;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="J-ZAKAT — {{ $tabTitle }} · Majlis Agama Islam Johor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tabTitle }} — J-ZAKAT</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
<div class="auth-shell"
     x-data="{
        tab: '{{ $initialTab }}',
        showPassword: false,
        showRegPassword: false,
        showRegPasswordConfirmation: false,
        submittingSignIn: false,
        submittingSignUp: false
     }">

    @include('auth._split-aside', ['context' => $mode])

    <main class="auth-panel">
        @include('auth._auth-topbar')

        <div class="auth-panel__content">
            <div class="auth-card">
                <div class="auth-card__head">
                    <span class="auth-badge">
                        <x-icon name="shield-check" :size="12" />
                        {{ $roleLabel }}
                    </span>
                    <h1 class="auth-title"
                        x-text="tab === 'signin' ? '{{ $signinTitle }}' : '{{ $signupTitle }}'">
                        {{ $displayTitle }}
                    </h1>
                    <p class="auth-subtitle"
                       x-text="tab === 'signin' ? '{{ $signinSubtitle }}' : '{{ $signupSubtitle }}'">
                        {{ $displaySubtitle }}
                    </p>
                </div>

                <div class="auth-tabs" role="tablist" aria-label="{{ __('auth.tabs.login') }} / {{ __('auth.tabs.register') }}">
                    <button type="button"
                            role="tab"
                            :aria-selected="tab === 'signin'"
                            @click="tab = 'signin'"
                            class="auth-tab {{ $initialTab === 'signin' ? 'is-active' : '' }}"
                            :class="{ 'is-active': tab === 'signin' }">
                        {{ __('auth.tabs.login') }}
                    </button>
                    <button type="button"
                            role="tab"
                            :aria-selected="tab === 'signup'"
                            @click="tab = 'signup'"
                            class="auth-tab {{ $initialTab === 'signup' ? 'is-active' : '' }}"
                            :class="{ 'is-active': tab === 'signup' }">
                        {{ __('auth.tabs.register') }}
                    </button>
                </div>

                <div class="auth-forms">
                    {{-- ============================================================ --}}
                    {{-- LOG MASUK --}}
                    {{-- ============================================================ --}}
                    <form method="POST" action="{{ $loginAction }}"
                          class="auth-form {{ $initialTab === 'signup' ? 'is-hidden' : '' }}"
                          :class="{ 'is-hidden': tab === 'signup' }"
                          novalidate
                          @submit="submittingSignIn = true">

                        @csrf
                        <input type="hidden" name="_form" value="signin">

                        <div class="auth-field">
                            <label for="signin-email" class="auth-label">{{ __('auth.fields.email') }}</label>
                            <div class="auth-input-wrap">
                                <x-icon name="mail" :size="18" class="auth-input-icon" />
                                <input id="signin-email" type="email" name="email" required
                                       autocomplete="email" placeholder="{{ __('auth.fields.email_placeholder') }}"
                                       value="{{ old('email') }}"
                                       {{ $initialTab === 'signin' ? 'autofocus' : '' }}
                                       @class(['auth-input', 'auth-input--icon', 'is-invalid' => $errors->has('email') && ! $hasRegisterOld])>
                            </div>
                            @if($errors->has('email') && ! $hasRegisterOld)
                                <p class="auth-error" role="alert">
                                    <x-icon name="circle-alert" :size="13" />
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                        </div>

                        <div class="auth-field">
                            <label for="signin-password" class="auth-label">{{ __('auth.fields.password') }}</label>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" :size="18" class="auth-input-icon" />
                                <input id="signin-password" type="password" name="password" required
                                       placeholder="{{ __('auth.fields.password_placeholder') }}"
                                       autocomplete="current-password"
                                       x-bind:type="showPassword ? 'text' : 'password'"
                                       @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password') && ! $hasRegisterOld])>
                                <button type="button" class="auth-eye"
                                        @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                    <span x-show="!showPassword"><x-icon name="eye" :size="18" /></span>
                                    <span x-show="showPassword" x-cloak><x-icon name="eye-off" :size="18" /></span>
                                </button>
                            </div>
                            @if($errors->has('password') && ! $hasRegisterOld)
                                <p class="auth-error" role="alert">
                                    <x-icon name="circle-alert" :size="13" />
                                    {{ $errors->first('password') }}
                                </p>
                            @endif
                        </div>

                        <div class="auth-row">
                            <label class="auth-check">
                                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                <span>{{ __('auth.login.remember') }}</span>
                            </label>
                            <a href="{{ $forgotUrl }}" class="auth-link">{{ __('auth.login.forgot') }}</a>
                        </div>

                        <button type="submit" class="auth-submit" :disabled="submittingSignIn">
                            <span x-show="!submittingSignIn">{{ __('auth.login.submit') }}</span>
                            <span x-show="submittingSignIn" x-cloak class="inline-flex items-center gap-2">
                                <span class="auth-spinner"></span>
                                {{ __('auth.login.processing') }}
                            </span>
                            <x-icon name="arrow-right" :size="17" x-show="!submittingSignIn" />
                        </button>

                        <p class="auth-switch">
                            {{ $isPortal ? __('auth.login.switch_portal') : __('auth.login.switch_admin') }}
                            <a href="{{ $otherLoginUrl }}">{{ $otherLoginText }}</a>
                        </p>
                    </form>

                    {{-- ============================================================ --}}
                    {{-- DAFTAR --}}
                    {{-- ============================================================ --}}
                    <form method="POST" action="{{ $registerAction }}"
                          class="auth-form {{ $initialTab === 'signup' ? '' : 'is-hidden' }}"
                          :class="{ 'is-hidden': tab !== 'signup' }"
                          novalidate
                          @submit="submittingSignUp = true">

                        @csrf
                        <input type="hidden" name="_form" value="signup">

                        @if($mode === 'admin')
                            <div class="auth-field">
                                <label for="reg-name" class="auth-label">{{ __('auth.fields.name') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="user" :size="18" class="auth-input-icon" />
                                    <input id="reg-name" type="text" name="name" required maxlength="255"
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
                                <label for="reg-email" class="auth-label">{{ __('auth.fields.email') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="mail" :size="18" class="auth-input-icon" />
                                    <input id="reg-email" type="email" name="email" required maxlength="255"
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
                                <label for="reg-invitation" class="auth-label">{{ __('auth.fields.invitation') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="key" :size="18" class="auth-input-icon" />
                                    <input id="reg-invitation" type="text" name="invitation_code" required maxlength="64"
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
                                <label for="reg-password" class="auth-label">{{ __('auth.fields.password') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password" type="password" name="password" required minlength="8"
                                           placeholder="{{ __('auth.fields.password_new_placeholder') }}" autocomplete="new-password"
                                           x-bind:type="showRegPassword ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPassword = !showRegPassword"
                                            :aria-label="showRegPassword ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                        <span x-show="!showRegPassword"><x-icon name="eye" :size="18" /></span>
                                        <span x-show="showRegPassword" x-cloak><x-icon name="eye-off" :size="18" /></span>
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
                                <label for="reg-password-confirmation" class="auth-label">{{ __('auth.fields.confirm_password') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                                           placeholder="{{ __('auth.fields.confirm_placeholder') }}" autocomplete="new-password"
                                           x-bind:type="showRegPasswordConfirmation ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPasswordConfirmation = !showRegPasswordConfirmation"
                                            :aria-label="showRegPasswordConfirmation ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                        <span x-show="!showRegPasswordConfirmation"><x-icon name="eye" :size="18" /></span>
                                        <span x-show="showRegPasswordConfirmation" x-cloak><x-icon name="eye-off" :size="18" /></span>
                                    </button>
                                </div>
                                @if($errors->has('password_confirmation'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('password_confirmation') }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="auth-field">
                                <label for="reg-name" class="auth-label">{{ __('auth.fields.name') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="user" :size="18" class="auth-input-icon" />
                                    <input id="reg-name" type="text" name="name" required maxlength="255"
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
                                <label for="reg-ic" class="auth-label">{{ __('auth.fields.ic') }}</label>
                                <input id="reg-ic" type="text" name="ic_no" required maxlength="12" inputmode="numeric"
                                       pattern="\d{12}" placeholder="{{ __('auth.fields.ic_placeholder') }}"
                                       value="{{ old('ic_no') }}"
                                       @class(['auth-input', 'is-invalid' => $errors->has('ic_no')])>
                                @if($errors->has('ic_no'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('ic_no') }}
                                    </p>
                                @endif
                            </div>

                            <div class="auth-grid">
                                <div class="auth-field">
                                    <label for="reg-dob" class="auth-label">{{ __('auth.fields.dob') }}</label>
                                    <input id="reg-dob" type="date" name="date_of_birth" required
                                           autocomplete="bday" value="{{ old('date_of_birth') }}"
                                           @class(['auth-input', 'is-invalid' => $errors->has('date_of_birth')])>
                                    @if($errors->has('date_of_birth'))
                                        <p class="auth-error" role="alert">
                                            <x-icon name="circle-alert" :size="13" />
                                            {{ $errors->first('date_of_birth') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="auth-field">
                                    <label for="reg-gender" class="auth-label">{{ __('auth.fields.gender') }}</label>
                                    <select id="reg-gender" name="gender" required
                                            @class(['auth-select', 'is-invalid' => $errors->has('gender')])>
                                        <option value="">{{ __('common.actions.select') }}</option>
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>{{ __('auth.fields.male') }}</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>{{ __('auth.fields.female') }}</option>
                                    </select>
                                    @if($errors->has('gender'))
                                        <p class="auth-error" role="alert">
                                            <x-icon name="circle-alert" :size="13" />
                                            {{ $errors->first('gender') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="reg-phone" class="auth-label">{{ __('auth.fields.phone') }}</label>
                                <input id="reg-phone" type="tel" name="phone" required maxlength="15"
                                       autocomplete="tel" placeholder="0123456789"
                                       value="{{ old('phone') }}"
                                       @class(['auth-input', 'is-invalid' => $errors->has('phone')])>
                                @if($errors->has('phone'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('phone') }}
                                    </p>
                                @endif
                            </div>

                            <div class="auth-field">
                                <label for="reg-email" class="auth-label">{{ __('auth.fields.email') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="mail" :size="18" class="auth-input-icon" />
                                    <input id="reg-email" type="email" name="email" required maxlength="255"
                                           autocomplete="email" placeholder="{{ __('auth.fields.email_placeholder_public') }}"
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
                                <label for="reg-address" class="auth-label">{{ __('auth.fields.address') }}</label>
                                <input id="reg-address" type="text" name="address" required maxlength="500"
                                       autocomplete="street-address" placeholder="{{ __('auth.fields.address_placeholder') }}"
                                       value="{{ old('address') }}"
                                       @class(['auth-input', 'is-invalid' => $errors->has('address')])>
                                @if($errors->has('address'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('address') }}
                                    </p>
                                @endif
                            </div>

                            <div class="auth-grid">
                                <div class="auth-field">
                                    <label for="reg-district" class="auth-label">{{ __('auth.fields.district') }}</label>
                                    <input id="reg-district" type="text" name="district" required maxlength="100"
                                           autocomplete="address-level2" placeholder="{{ __('auth.fields.district_placeholder') }}"
                                           value="{{ old('district') }}"
                                           @class(['auth-input', 'is-invalid' => $errors->has('district')])>
                                    @if($errors->has('district'))
                                        <p class="auth-error" role="alert">
                                            <x-icon name="circle-alert" :size="13" />
                                            {{ $errors->first('district') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="auth-field">
                                    <label for="reg-state" class="auth-label">{{ __('auth.fields.state') }}</label>
                                    <select id="reg-state" name="state" required
                                            @class(['auth-select', 'is-invalid' => $errors->has('state')])>
                                        <option value="">{{ __('common.actions.select') }}</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('state'))
                                        <p class="auth-error" role="alert">
                                            <x-icon name="circle-alert" :size="13" />
                                            {{ $errors->first('state') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="reg-postcode" class="auth-label">{{ __('auth.fields.postcode') }}</label>
                                <input id="reg-postcode" type="text" name="postcode" required maxlength="5" inputmode="numeric"
                                       pattern="\d{5}" autocomplete="postal-code" placeholder="{{ __('auth.fields.postcode_placeholder') }}"
                                       value="{{ old('postcode') }}"
                                       @class(['auth-input', 'is-invalid' => $errors->has('postcode')])>
                                @if($errors->has('postcode'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('postcode') }}
                                    </p>
                                @endif
                            </div>

                            <div class="auth-field">
                                <label for="reg-password" class="auth-label">{{ __('auth.fields.password') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password" type="password" name="password" required minlength="8"
                                           placeholder="{{ __('auth.fields.password_new_placeholder') }}" autocomplete="new-password"
                                           x-bind:type="showRegPassword ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPassword = !showRegPassword"
                                            :aria-label="showRegPassword ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                        <span x-show="!showRegPassword"><x-icon name="eye" :size="18" /></span>
                                        <span x-show="showRegPassword" x-cloak><x-icon name="eye-off" :size="18" /></span>
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
                                <label for="reg-password-confirmation" class="auth-label">{{ __('auth.fields.confirm_password') }}</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                                           placeholder="{{ __('auth.fields.confirm_placeholder') }}" autocomplete="new-password"
                                           x-bind:type="showRegPasswordConfirmation ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPasswordConfirmation = !showRegPasswordConfirmation"
                                            :aria-label="showRegPasswordConfirmation ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'">
                                        <span x-show="!showRegPasswordConfirmation"><x-icon name="eye" :size="18" /></span>
                                        <span x-show="showRegPasswordConfirmation" x-cloak><x-icon name="eye-off" :size="18" /></span>
                                    </button>
                                </div>
                                @if($errors->has('password_confirmation'))
                                    <p class="auth-error" role="alert">
                                        <x-icon name="circle-alert" :size="13" />
                                        {{ $errors->first('password_confirmation') }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <label class="auth-check auth-check--top">
                            <input type="checkbox" required>
                            <span>
                                {{ __('auth.terms') }}
                            </span>
                        </label>

                        <button type="submit" class="auth-submit" :disabled="submittingSignUp">
                            <span x-show="!submittingSignUp">{{ __('auth.login.signup_submit') }}</span>
                            <span x-show="submittingSignUp" x-cloak class="inline-flex items-center gap-2">
                                <span class="auth-spinner"></span>
                                {{ __('auth.login.processing') }}
                            </span>
                            <x-icon name="arrow-right" :size="17" x-show="!submittingSignUp" />
                        </button>

                        <p class="auth-switch">
                            {{ __('auth.login.has_account') }}
                            <a href="#" @click.prevent="tab = 'signin'">{{ __('auth.login.signup_login') }}</a>
                        </p>
                    </form>
                </div>
            </div>

            @include('auth._auth-footer')
        </div>
    </main>
</div>
</body>
</html>
