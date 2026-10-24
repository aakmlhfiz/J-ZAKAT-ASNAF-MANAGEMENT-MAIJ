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
    $otherLoginText = $isPortal ? 'Log masuk pentadbir' : 'Log masuk pemohon';

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
        ? ($isPortal ? 'Daftar Pemohon' : 'Daftar Pentadbir')
        : ($isPortal ? 'Log Masuk Pemohon' : 'Log Masuk');

    $roleLabel = $isPortal ? 'Portal Pemohon' : 'Portal Pentadbir';
    $signinTitle = 'Selamat kembali';
    $signinSubtitle = $isPortal
        ? 'Log masuk untuk menyemak permohonan dan status bantuan anda.'
        : 'Log masuk untuk meneruskan pengurusan asnaf dan permohonan.';
    $signupTitle = $isPortal ? 'Daftar akaun pemohon' : 'Daftar akaun pentadbir';
    $signupSubtitle = $isPortal
        ? 'Lengkapkan maklumat anda untuk memohon bantuan zakat.'
        : 'Gunakan kod undangan yang diberikan oleh pentadbir sistem.';
    $displayTitle = $initialTab === 'signup' ? $signupTitle : $signinTitle;
    $displaySubtitle = $initialTab === 'signup' ? $signupSubtitle : $signinSubtitle;
@endphp
<!DOCTYPE html>
<html lang="ms" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#070D1A">
    <meta name="description" content="J-ZAKAT AI — {{ $tabTitle }} Majlis Agama Islam Johor">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tabTitle }} — J-ZAKAT AI</title>
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

                <div class="auth-tabs" role="tablist" aria-label="Pilih log masuk atau pendaftaran">
                    <button type="button"
                            role="tab"
                            :aria-selected="tab === 'signin'"
                            @click="tab = 'signin'"
                            class="auth-tab {{ $initialTab === 'signin' ? 'is-active' : '' }}"
                            :class="{ 'is-active': tab === 'signin' }">
                        Log Masuk
                    </button>
                    <button type="button"
                            role="tab"
                            :aria-selected="tab === 'signup'"
                            @click="tab = 'signup'"
                            class="auth-tab {{ $initialTab === 'signup' ? 'is-active' : '' }}"
                            :class="{ 'is-active': tab === 'signup' }">
                        Daftar
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
                            <label for="signin-email" class="auth-label">Emel</label>
                            <div class="auth-input-wrap">
                                <x-icon name="mail" :size="18" class="auth-input-icon" />
                                <input id="signin-email" type="email" name="email" required
                                       autocomplete="email" placeholder="nama@maij.gov.my"
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
                            <label for="signin-password" class="auth-label">Kata laluan</label>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" :size="18" class="auth-input-icon" />
                                <input id="signin-password" type="password" name="password" required
                                       placeholder="Masukkan kata laluan"
                                       autocomplete="current-password"
                                       x-bind:type="showPassword ? 'text' : 'password'"
                                       @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password') && ! $hasRegisterOld])>
                                <button type="button" class="auth-eye"
                                        @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                                <span>Kekal log masuk</span>
                            </label>
                            <a href="{{ $forgotUrl }}" class="auth-link">Lupa kata laluan?</a>
                        </div>

                        <button type="submit" class="auth-submit" :disabled="submittingSignIn">
                            <span x-show="!submittingSignIn">Log Masuk</span>
                            <span x-show="submittingSignIn" x-cloak class="inline-flex items-center gap-2">
                                <span class="auth-spinner"></span>
                                Memproses...
                            </span>
                            <x-icon name="arrow-right" :size="17" x-show="!submittingSignIn" />
                        </button>

                        <p class="auth-switch">
                            {{ $isPortal ? 'Pentadbir MAIJ?' : 'Pemohon bantuan?' }}
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
                                <label for="reg-name" class="auth-label">Nama penuh</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="user" :size="18" class="auth-input-icon" />
                                    <input id="reg-name" type="text" name="name" required maxlength="255"
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
                                <label for="reg-email" class="auth-label">Emel</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="mail" :size="18" class="auth-input-icon" />
                                    <input id="reg-email" type="email" name="email" required maxlength="255"
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
                                <label for="reg-invitation" class="auth-label">Kod undangan pentadbir</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="key" :size="18" class="auth-input-icon" />
                                    <input id="reg-invitation" type="text" name="invitation_code" required maxlength="64"
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
                                <label for="reg-password" class="auth-label">Kata laluan</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password" type="password" name="password" required minlength="8"
                                           placeholder="Minimum 8 aksara" autocomplete="new-password"
                                           x-bind:type="showRegPassword ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPassword = !showRegPassword"
                                            :aria-label="showRegPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                                    <p class="auth-hint">Minimum 8 aksara. Gunakan gabungan huruf dan nombor.</p>
                                @endif
                            </div>

                            <div class="auth-field">
                                <label for="reg-password-confirmation" class="auth-label">Sahkan kata laluan</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                                           placeholder="Ulang kata laluan" autocomplete="new-password"
                                           x-bind:type="showRegPasswordConfirmation ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPasswordConfirmation = !showRegPasswordConfirmation"
                                            :aria-label="showRegPasswordConfirmation ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                                <label for="reg-name" class="auth-label">Nama penuh</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="user" :size="18" class="auth-input-icon" />
                                    <input id="reg-name" type="text" name="name" required maxlength="255"
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
                                <label for="reg-ic" class="auth-label">No Kad Pengenalan</label>
                                <input id="reg-ic" type="text" name="ic_no" required maxlength="12" inputmode="numeric"
                                       pattern="\d{12}" placeholder="12 digit tanpa sengkang"
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
                                    <label for="reg-dob" class="auth-label">Tarikh lahir</label>
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
                                    <label for="reg-gender" class="auth-label">Jantina</label>
                                    <select id="reg-gender" name="gender" required
                                            @class(['auth-select', 'is-invalid' => $errors->has('gender')])>
                                        <option value="">Pilih</option>
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Lelaki</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
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
                                <label for="reg-phone" class="auth-label">No telefon</label>
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
                                <label for="reg-email" class="auth-label">Emel</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="mail" :size="18" class="auth-input-icon" />
                                    <input id="reg-email" type="email" name="email" required maxlength="255"
                                           autocomplete="email" placeholder="nama@contoh.com"
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
                                <label for="reg-address" class="auth-label">Alamat</label>
                                <input id="reg-address" type="text" name="address" required maxlength="500"
                                       autocomplete="street-address" placeholder="Alamat lengkap"
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
                                    <label for="reg-district" class="auth-label">Daerah</label>
                                    <input id="reg-district" type="text" name="district" required maxlength="100"
                                           autocomplete="address-level2" placeholder="Daerah"
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
                                    <label for="reg-state" class="auth-label">Negeri</label>
                                    <select id="reg-state" name="state" required
                                            @class(['auth-select', 'is-invalid' => $errors->has('state')])>
                                        <option value="">Pilih</option>
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
                                <label for="reg-postcode" class="auth-label">Poskod</label>
                                <input id="reg-postcode" type="text" name="postcode" required maxlength="5" inputmode="numeric"
                                       pattern="\d{5}" autocomplete="postal-code" placeholder="5 digit"
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
                                <label for="reg-password" class="auth-label">Kata laluan</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password" type="password" name="password" required minlength="8"
                                           placeholder="Minimum 8 aksara" autocomplete="new-password"
                                           x-bind:type="showRegPassword ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPassword = !showRegPassword"
                                            :aria-label="showRegPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                                    <p class="auth-hint">Minimum 8 aksara. Gunakan gabungan huruf dan nombor.</p>
                                @endif
                            </div>

                            <div class="auth-field">
                                <label for="reg-password-confirmation" class="auth-label">Sahkan kata laluan</label>
                                <div class="auth-input-wrap">
                                    <x-icon name="lock" :size="18" class="auth-input-icon" />
                                    <input id="reg-password-confirmation" type="password" name="password_confirmation" required minlength="8"
                                           placeholder="Ulang kata laluan" autocomplete="new-password"
                                           x-bind:type="showRegPasswordConfirmation ? 'text' : 'password'"
                                           @class(['auth-input', 'auth-input--icon', 'auth-input--password', 'is-invalid' => $errors->has('password_confirmation')])>
                                    <button type="button" class="auth-eye"
                                            @click="showRegPasswordConfirmation = !showRegPasswordConfirmation"
                                            :aria-label="showRegPasswordConfirmation ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'">
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
                                Saya bersetuju dengan Terma &amp; Syarat serta Notis Privasi J-ZAKAT AI.
                            </span>
                        </label>

                        <button type="submit" class="auth-submit" :disabled="submittingSignUp">
                            <span x-show="!submittingSignUp">Daftar Akaun</span>
                            <span x-show="submittingSignUp" x-cloak class="inline-flex items-center gap-2">
                                <span class="auth-spinner"></span>
                                Memproses...
                            </span>
                            <x-icon name="arrow-right" :size="17" x-show="!submittingSignUp" />
                        </button>

                        <p class="auth-switch">
                            Sudah mempunyai akaun?
                            <a href="#" @click.prevent="tab = 'signin'">Log masuk di sini</a>
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
