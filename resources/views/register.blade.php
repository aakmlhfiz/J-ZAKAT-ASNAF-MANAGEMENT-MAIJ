{{-- Admin registration — split-auth design matching /login --}}
<!DOCTYPE html>
<html lang="ms" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1220">
    <meta name="description" content="Daftar Akaun Pentadbir — J-ZAKAT AI">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akaun Pentadbir — J-ZAKAT AI</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased">
<div class="split-auth" x-data="{ googleMsg: '', google() { this.googleMsg = 'Pendaftaran dengan Google belum dikonfigurasi buat masa ini.'; setTimeout(() => this.googleMsg = '', 4500); } }">

    <aside class="split-auth__left" aria-hidden="true">
        <div class="split-auth__left-caption">
            <strong>MAIJ · J-ZAKAT AI</strong>
            Majlis Agama Islam Johor
        </div>
    </aside>

    <main class="split-auth__right">
        @include('auth._split-brand')
        <div class="split-auth__tabs">
            <a href="{{ route('login') }}" class="split-auth__tab">Sign In</a>
            <a href="{{ route('register') }}" class="split-auth__tab is-active">Sign Up</a>
        </div>

        <div class="split-auth__forms">
            <form method="POST" action="{{ route('register') }}" class="split-auth__form" novalidate>
                @csrf

                <div class="sa-field">
                    <label for="name" class="sa-label">Nama penuh</label>
                    <input id="name" type="text" name="name" required maxlength="255" autocomplete="name"
                           value="{{ old('name') }}"
                           @class(['sa-input', 'is-invalid' => $errors->has('name')])>
                    @if($errors->has('name'))<p class="sa-error">{{ $errors->first('name') }}</p>@endif
                </div>

                <div class="sa-field">
                    <label for="email" class="sa-label">Your email</label>
                    <input id="email" type="email" name="email" required maxlength="255" autocomplete="email"
                           value="{{ old('email') }}"
                           @class(['sa-input', 'is-invalid' => $errors->has('email')])>
                    @if($errors->has('email'))<p class="sa-error">{{ $errors->first('email') }}</p>@endif
                </div>

                <div class="sa-field">
                    <label for="invitation_code" class="sa-label">Kod undangan pentadbir</label>
                    <input id="invitation_code" type="text" name="invitation_code" required maxlength="64"
                           placeholder="MAIJ-XXXX-XXXX-XXXX-XXXX"
                           value="{{ old('invitation_code') }}"
                           @class(['sa-input', 'is-invalid' => $errors->has('invitation_code')])>
                    @if($errors->has('invitation_code'))
                        <p class="sa-error">{{ $errors->first('invitation_code') }}</p>
                    @else
                        <p class="text-[11.5px] text-white/45 mt-2 flex items-center gap-1.5"><x-icon name="lock" :size="12" /> Kod undangan diberikan oleh pentadbir sistem.</p>
                    @endif
                </div>

                <div class="sa-field">
                    <label for="password" class="sa-label">Password</label>
                    <input id="password" type="password" name="password" required minlength="8" placeholder="Minimum 8 aksara"
                           autocomplete="new-password"
                           @class(['sa-input', 'is-invalid' => $errors->has('password')])>
                    @if($errors->has('password'))<p class="sa-error">{{ $errors->first('password') }}</p>@endif
                </div>

                <div class="sa-field">
                    <label for="password_confirmation" class="sa-label">Sahkan password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                           autocomplete="new-password"
                           @class(['sa-input', 'is-invalid' => $errors->has('password_confirmation')])>
                    @if($errors->has('password_confirmation'))<p class="sa-error">{{ $errors->first('password_confirmation') }}</p>@endif
                </div>

                <label class="sa-check mb-5" style="align-items:flex-start;">
                    <input type="checkbox" required class="mt-0.5">
                    <span class="leading-relaxed">Saya bersetuju dengan <a href="#" class="sa-link">Terma &amp; Syarat</a> serta <a href="#" class="sa-link">Notis Privasi</a> J-ZAKAT AI.</span>
                </label>

                <button type="submit" class="btn btn-lg btn-gradient w-full">Sign Up</button>

                <div class="sa-divider"><span>or</span></div>

                <button type="button" class="btn btn-lg btn-glass w-full" @click.prevent="google()">
                    <svg class="sa-google-g" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571.001-.001.002-.001.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    Sign Up with Google
                </button>

                <p class="sa-info" x-show="googleMsg" x-text="googleMsg" x-transition x-cloak></p>

                <p class="sa-back-to-home">
                    Sudah mempunyai akaun? <a href="{{ route('login') }}">Log Masuk</a>
                </p>
            </form>
        </div>

        <footer class="split-auth__footer">
            <div>
                <a href="#">Privacy</a><span class="dot">·</span>
                <a href="#">Terms</a><span class="dot">·</span>
                <a href="#">About</a>
            </div>
            <p class="mt-2 text-[11px] text-white/40">&copy; {{ date('Y') }} J-ZAKAT AI · Majlis Agama Islam Johor</p>
        </footer>
    </main>
</div>
</body>
</html>
