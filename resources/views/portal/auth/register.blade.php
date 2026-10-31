{{-- Applicant registration — modern split auth matching /user/login --}}
@php
    $states = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Putrajaya','Labuan'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="{{ __('auth.register.portal.title') }} — J-ZAKAT">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.register.portal.title') }} — J-ZAKAT</title>
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

    @include('auth._split-aside', ['context' => 'portal'])

    <main class="auth-panel">
        @include('auth._auth-topbar')

        <div class="auth-panel__content">
            <div class="auth-card auth-card--wide">
                <div class="auth-card__head">
                    <span class="auth-badge">
                        <x-icon name="hand-heart" :size="12" />
                        {{ __('auth.register.portal.badge') }}
                    </span>
                    <h1 class="auth-title">{{ __('auth.register.portal.title') }}</h1>
                    <p class="auth-subtitle">{{ __('auth.register.portal.subtitle') }}</p>
                </div>

                <div class="auth-tabs" aria-label="{{ __('auth.tabs.login') }} / {{ __('auth.tabs.register') }}">
                    <a href="{{ route('user.login') }}" class="auth-tab">{{ __('auth.tabs.login') }}</a>
                    <a href="{{ route('user.register') }}" class="auth-tab is-active">{{ __('auth.tabs.register') }}</a>
                </div>

                <form method="POST" action="{{ route('user.register') }}" class="auth-form" novalidate
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
                        <label for="ic_no" class="auth-label">{{ __('auth.fields.ic') }}</label>
                        <input id="ic_no" type="text" name="ic_no" required maxlength="12" inputmode="numeric"
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
                            <label for="date_of_birth" class="auth-label">{{ __('auth.fields.dob') }}</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" required
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
                            <label for="gender" class="auth-label">{{ __('auth.fields.gender') }}</label>
                            <select id="gender" name="gender" required
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
                        <label for="phone" class="auth-label">{{ __('auth.fields.phone') }}</label>
                        <input id="phone" type="tel" name="phone" required maxlength="15"
                               autocomplete="tel" placeholder="{{ __('auth.fields.phone_placeholder') }}"
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
                        <label for="email" class="auth-label">{{ __('auth.fields.email') }}</label>
                        <div class="auth-input-wrap">
                            <x-icon name="mail" :size="18" class="auth-input-icon" />
                            <input id="email" type="email" name="email" required maxlength="255"
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
                        <label for="address" class="auth-label">{{ __('auth.fields.address') }}</label>
                        <input id="address" type="text" name="address" required maxlength="500"
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
                            <label for="district" class="auth-label">{{ __('auth.fields.district') }}</label>
                            <input id="district" type="text" name="district" required maxlength="100"
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
                            <label for="state" class="auth-label">{{ __('auth.fields.state') }}</label>
                            <select id="state" name="state" required
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
                        <label for="postcode" class="auth-label">{{ __('auth.fields.postcode') }}</label>
                        <input id="postcode" type="text" name="postcode" required maxlength="5" inputmode="numeric"
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
                        <span x-show="!submitting">{{ __('auth.register.portal.submit') }}</span>
                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                            <span class="auth-spinner"></span>
                            {{ __('auth.login.processing') }}
                        </span>
                        <x-icon name="arrow-right" :size="17" x-show="!submitting" />
                    </button>

                    <p class="auth-switch">
                        {{ __('auth.register.portal.has_account') }}
                        <a href="{{ route('user.login') }}">{{ __('auth.register.portal.login_link') }}</a>
                    </p>
                </form>
            </div>

            @include('auth._auth-footer')
        </div>
    </main>
</div>
</body>
</html>
