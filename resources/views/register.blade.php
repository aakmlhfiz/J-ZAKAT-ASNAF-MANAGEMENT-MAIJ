{{-- Pendaftaran pentadbir — modern split auth matching /login --}}
<!DOCTYPE html>
<html lang="ms" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="Daftar Akaun Pentadbir — J-ZAKAT AI">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akaun Pentadbir — J-ZAKAT AI</title>
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
                        Pendaftaran Pentadbir
                    </span>
                    <h1 class="auth-title">Daftar akaun pentadbir</h1>
                    <p class="auth-subtitle">
                        Gunakan kod undangan yang diberikan oleh pentadbir sistem untuk membuka akses.
                    </p>
                </div>

                <div class="auth-tabs" aria-label="Pilih log masuk atau pendaftaran">
                    <a href="{{ route('login') }}" class="auth-tab">Log Masuk</a>
                    <a href="{{ route('register') }}" class="auth-tab is-active">Daftar</a>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate
                      @submit="submitting = true">
                    @csrf

                    <div class="auth-field">
                        <label for="name" class="auth-label">Nama penuh</label>
                        <div class="auth-input-wrap">
                            <x-icon name="user" :size="18" class="auth-input-icon" />
                            <input id="name" type="text" name="name" required maxlength="255"
                                   autocomplete="name" placeholder="Nama seperti dalam kad pengenalan"
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
                        <label for="email" class="auth-label">Emel</label>
                        <div class="auth-input-wrap">
                            <x-icon name="mail" :size="18" class="auth-input-icon" />
                            <input id="email" type="email" name="email" required maxlength="255"
                                   autocomplete="email" placeholder="nama@maij.gov.my"
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
                        <label for="invitation_code" class="auth-label">Kod undangan pentadbir</label>
                        <div class="auth-input-wrap">
                            <x-icon name="key" :size="18" class="auth-input-icon" />
                            <input id="invitation_code" type="text" name="invitation_code" required maxlength="64"
                                   placeholder="MAIJ-XXXX-XXXX-XXXX-XXXX"
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
                                Kod undangan diberikan oleh pentadbir sistem.
                            </p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">Kata laluan</label>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" :size="18" class="auth-input-icon" />
                            <input id="password" type="password" name="password" required minlength="8"
                                   placeholder="Minimum 8 aksara" autocomplete="new-password"
                                   x-bind:type="showPassword ? 'text' : 'password'"
                                   @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                            <button type="button" class="auth-eye"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                            <p class="auth-hint">Minimum 8 aksara. Gunakan gabungan huruf dan nombor.</p>
                        @endif
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation" class="auth-label">Sahkan kata laluan</label>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" :size="18" class="auth-input-icon" />
                            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                                   placeholder="Ulang kata laluan" autocomplete="new-password"
                                   x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                   @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                            <button type="button" class="auth-eye"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    :aria-label="showPasswordConfirmation ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                        <span>
                            Saya bersetuju dengan Terma &amp; Syarat serta Notis Privasi J-ZAKAT AI.
                        </span>
                    </label>

                    <button type="submit" class="auth-submit" :disabled="submitting">
                        <span x-show="!submitting">Daftar Akaun</span>
                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                            <span class="auth-spinner"></span>
                            Memproses...
                        </span>
                        <x-icon name="arrow-right" :size="17" x-show="!submitting" />
                    </button>

                    <p class="auth-switch">
                        Sudah mempunyai akaun?
                        <a href="{{ route('login') }}">Log masuk di sini</a>
                    </p>
                </form>
            </div>

            @include('auth._auth-footer')
        </div>
    </main>
</div>
</body>
</html>
