{{--
    Split-panel login (admin + portal) inspired by the Sign In / Sign Up
    dual-tab auth design. Self-contained Blade: full HTML, no layout extend.

    Mode handling (passed from controller / route):
      $mode = 'admin'  → POST to route('login') / route('register')
      $mode = 'portal' → POST to route('user.login') / route('user.register')

    Auto-opens the Sign Up tab when the user was redirected back with
    register-field errors (e.g. ?tab=signup, or old('invitation_code'),
    or old('ic_no') present) so they land back in the right tab.
--}}
@php
    $mode = $mode ?? 'admin';

    $loginAction   = $mode === 'portal' ? route('user.login')  : route('login');
    $registerAction = $mode === 'portal' ? route('user.register') : route('register');
    $forgotUrl     = $mode === 'portal' ? route('user.password.request') : route('password.request');
    $homeUrl       = $mode === 'portal' ? route('user.dashboard') : route('dashboard');
    $otherLoginUrl = $mode === 'portal' ? route('login') : route('user.login');

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
        ? ($mode === 'portal' ? 'Daftar Pemohon' : 'Daftar Pentadbir')
        : ($mode === 'portal' ? 'Log Masuk Pemohon' : 'Log Masuk');
@endphp
<!DOCTYPE html>
<html lang="ms" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2d3849">
    <meta name="description" content="J-ZAKAT AI — {{ $tabTitle }} · Majlis Agama Islam Johor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tabTitle }} — J-ZAKAT AI</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased">
<div class="split-auth"
     x-data="{
        tab: '{{ $initialTab }}',
        googleMsg: '',
        openSignup() { this.tab = 'signup'; this.googleMsg = ''; },
        openSignin() { this.tab = 'signin'; this.googleMsg = ''; },
        google() {
            this.googleMsg = 'Log masuk dengan Google belum dikonfigurasi buat masa ini.';
            setTimeout(() => { this.googleMsg = ''; }, 4500);
        }
     }">

    {{-- LEFT: photo panel (hidden on mobile) --}}
    <aside class="split-auth__left" aria-hidden="true">
        <div class="split-auth__left-caption">
            <strong>MAIJ · J-ZAKAT AI</strong>
            Majlis Agama Islam Johor
        </div>
    </aside>

    {{-- RIGHT: form panel --}}
    <main class="split-auth__right">

        @include('auth._split-brand')

        {{-- Tabs --}}
        <div class="split-auth__tabs" role="tablist" aria-label="Authentication mode">
            <button type="button"
                    role="tab"
                    :aria-selected="tab === 'signin'"
                    @click="openSignin()"
                    class="split-auth__tab {{ $initialTab === 'signin' ? 'is-active' : '' }}"
                    :class="{ 'is-active': tab === 'signin' }">
                Sign In
            </button>
            <button type="button"
                    role="tab"
                    :aria-selected="tab === 'signup'"
                    @click="openSignup()"
                    class="split-auth__tab {{ $initialTab === 'signup' ? 'is-active' : '' }}"
                    :class="{ 'is-active': tab === 'signup' }">
                Sign Up
            </button>
        </div>

        {{-- Forms (scrollable) --}}
        <div class="split-auth__forms">

            {{-- ============================================================ --}}
            {{-- SIGN IN --}}
            {{-- ============================================================ --}}
            <form method="POST" action="{{ $loginAction }}"
                  class="split-auth__form {{ $initialTab === 'signup' ? 'is-hidden' : '' }}"
                  :class="{ 'is-hidden': tab === 'signup' }"
                  novalidate>

                @csrf
                <input type="hidden" name="_form" value="signin">

                <div class="sa-field">
                    <label for="signin-email" class="sa-label">Your email</label>
                    <input id="signin-email" type="email" name="email" required
                           autocomplete="email" placeholder="name@maij.gov.my"
                           value="{{ old('email') }}"
                           @class(['sa-input', 'is-invalid' => $errors->has('email') && ! $hasRegisterOld])>
                    @if($errors->has('email') && ! $hasRegisterOld)
                        <p class="sa-error">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <div class="sa-field">
                    <label for="signin-password" class="sa-label">Your password</label>
                    <input id="signin-password" type="password" name="password" required
                           placeholder="••••••••"
                           autocomplete="current-password"
                           @class(['sa-input', 'is-invalid' => $errors->has('password') && ! $hasRegisterOld])>
                    @if($errors->has('password') && ! $hasRegisterOld)
                        <p class="sa-error">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                <div class="sa-row">
                    <label class="sa-check">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Keep me logged in</span>
                    </label>
                    <a href="{{ $forgotUrl }}" class="sa-link">Forgot password?</a>
                </div>

                @if($errors->has('email') && ! $hasRegisterOld)
                    <div class="sa-alert">{{ $errors->first('email') }}</div>
                @endif

                <button type="submit" class="btn btn-lg btn-gradient w-full">Sign In</button>

                <div class="sa-divider"><span>or</span></div>

                <button type="button" class="btn btn-lg btn-glass w-full" @click.prevent="google()">
                    <svg class="sa-google-g" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571.001-.001.002-.001.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    Sign In with Google
                </button>

                <p class="sa-info" x-show="googleMsg" x-text="googleMsg" x-transition x-cloak></p>

                <p class="sa-back-to-home">
                    <a href="{{ url('/') }}">← Kembali ke halaman utama</a>
                </p>
            </form>

            {{-- ============================================================ --}}
            {{-- SIGN UP --}}
            {{-- ============================================================ --}}
            <form method="POST" action="{{ $registerAction }}"
                  class="split-auth__form {{ $initialTab === 'signup' ? '' : 'is-hidden' }}"
                  :class="{ 'is-hidden': tab !== 'signup' }"
                  novalidate>

                @csrf
                <input type="hidden" name="_form" value="signup">

                @if($mode === 'admin')
                    {{-- Admin register: name + email + invitation_code + password --}}
                    <div class="sa-field">
                        <label for="reg-name" class="sa-label">Nama penuh</label>
                        <input id="reg-name" type="text" name="name" required maxlength="255"
                               value="{{ old('name') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('name')])>
                        @if($errors->has('name'))<p class="sa-error">{{ $errors->first('name') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-email" class="sa-label">Your email</label>
                        <input id="reg-email" type="email" name="email" required maxlength="255"
                               value="{{ old('email') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('email')])>
                        @if($errors->has('email'))<p class="sa-error">{{ $errors->first('email') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-invitation" class="sa-label">Kod undangan pentadbir</label>
                        <input id="reg-invitation" type="text" name="invitation_code" required maxlength="64"
                               placeholder="MAIJ-XXXX-XXXX-XXXX-XXXX"
                               value="{{ old('invitation_code') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('invitation_code')])>
                        @if($errors->has('invitation_code'))<p class="sa-error">{{ $errors->first('invitation_code') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-password" class="sa-label">Password</label>
                        <input id="reg-password" type="password" name="password" required minlength="8"
                               placeholder="Minimum 8 aksara"
                               @class(['sa-input', 'is-invalid' => $errors->has('password')])>
                        @if($errors->has('password'))<p class="sa-error">{{ $errors->first('password') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-password-confirmation" class="sa-label">Sahkan password</label>
                        <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                               @class(['sa-input', 'is-invalid' => $errors->has('password_confirmation')])>
                        @if($errors->has('password_confirmation'))<p class="sa-error">{{ $errors->first('password_confirmation') }}</p>@endif
                    </div>

                @else
                    {{-- Portal register: full applicant fields --}}
                    <div class="sa-field">
                        <label for="reg-name" class="sa-label">Nama penuh</label>
                        <input id="reg-name" type="text" name="name" required maxlength="255"
                               value="{{ old('name') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('name')])>
                        @if($errors->has('name'))<p class="sa-error">{{ $errors->first('name') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-ic" class="sa-label">No Kad Pengenalan (12 digit)</label>
                        <input id="reg-ic" type="text" name="ic_no" required maxlength="12" pattern="\d{12}"
                               placeholder="800315015678"
                               value="{{ old('ic_no') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('ic_no')])>
                        @if($errors->has('ic_no'))<p class="sa-error">{{ $errors->first('ic_no') }}</p>@endif
                    </div>

                    <div class="sa-grid">
                        <div class="sa-field">
                            <label for="reg-dob" class="sa-label">Tarikh lahir</label>
                            <input id="reg-dob" type="date" name="date_of_birth" required
                                   value="{{ old('date_of_birth') }}"
                                   @class(['sa-input', 'is-invalid' => $errors->has('date_of_birth')])>
                            @if($errors->has('date_of_birth'))<p class="sa-error">{{ $errors->first('date_of_birth') }}</p>@endif
                        </div>
                        <div class="sa-field">
                            <label for="reg-gender" class="sa-label">Jantina</label>
                            <select id="reg-gender" name="gender" required
                                    @class(['sa-select', 'is-invalid' => $errors->has('gender')])>
                                <option value="">— Pilih —</option>
                                <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Lelaki</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @if($errors->has('gender'))<p class="sa-error">{{ $errors->first('gender') }}</p>@endif
                        </div>
                    </div>

                    <div class="sa-field">
                        <label for="reg-phone" class="sa-label">No Telefon</label>
                        <input id="reg-phone" type="tel" name="phone" required maxlength="15"
                               placeholder="0123456789"
                               value="{{ old('phone') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('phone')])>
                        @if($errors->has('phone'))<p class="sa-error">{{ $errors->first('phone') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-email" class="sa-label">Your email</label>
                        <input id="reg-email" type="email" name="email" required maxlength="255"
                               value="{{ old('email') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('email')])>
                        @if($errors->has('email'))<p class="sa-error">{{ $errors->first('email') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-address" class="sa-label">Alamat</label>
                        <input id="reg-address" type="text" name="address" required maxlength="500"
                               value="{{ old('address') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('address')])>
                        @if($errors->has('address'))<p class="sa-error">{{ $errors->first('address') }}</p>@endif
                    </div>

                    <div class="sa-grid">
                        <div class="sa-field">
                            <label for="reg-district" class="sa-label">Daerah</label>
                            <input id="reg-district" type="text" name="district" required maxlength="100"
                                   value="{{ old('district') }}"
                                   @class(['sa-input', 'is-invalid' => $errors->has('district')])>
                            @if($errors->has('district'))<p class="sa-error">{{ $errors->first('district') }}</p>@endif
                        </div>
                        <div class="sa-field">
                            <label for="reg-state" class="sa-label">Negeri</label>
                            <select id="reg-state" name="state" required
                                    @class(['sa-select', 'is-invalid' => $errors->has('state')])>
                                <option value="">— Pilih —</option>
                                @foreach($states as $s)
                                    <option value="{{ $s }}" {{ old('state') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('state'))<p class="sa-error">{{ $errors->first('state') }}</p>@endif
                        </div>
                    </div>

                    <div class="sa-field">
                        <label for="reg-postcode" class="sa-label">Poskod (5 digit)</label>
                        <input id="reg-postcode" type="text" name="postcode" required maxlength="5" pattern="\d{5}"
                               value="{{ old('postcode') }}"
                               @class(['sa-input', 'is-invalid' => $errors->has('postcode')])>
                        @if($errors->has('postcode'))<p class="sa-error">{{ $errors->first('postcode') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-password" class="sa-label">Password</label>
                        <input id="reg-password" type="password" name="password" required minlength="8"
                               placeholder="Minimum 8 aksara"
                               @class(['sa-input', 'is-invalid' => $errors->has('password')])>
                        @if($errors->has('password'))<p class="sa-error">{{ $errors->first('password') }}</p>@endif
                    </div>

                    <div class="sa-field">
                        <label for="reg-password-confirmation" class="sa-label">Sahkan password</label>
                        <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                               @class(['sa-input', 'is-invalid' => $errors->has('password_confirmation')])>
                        @if($errors->has('password_confirmation'))<p class="sa-error">{{ $errors->first('password_confirmation') }}</p>@endif
                    </div>
                @endif

                @if($registerErrorBag && $registerErrorBag->isNotEmpty() && ! $errors->has('email') && ! $errors->has('name'))
                    {{-- generic register-level error --}}
                @endif

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
            </form>

        </div>

        {{-- Footer (Privacy · Terms · About) --}}
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
