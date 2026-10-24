@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', 'Pengesahan 2FA — J-ZAKAT AI')

@section('content')
    <div class="auth-card__head auth-card__head--center">
        <span class="auth-badge">
            <x-icon name="shield-check" :size="12" />
            Pengesahan Dua Faktor
        </span>
        <h1 class="auth-title">Sahkan identiti anda</h1>
        <p class="auth-subtitle">
            Masukkan kod 6 digit daripada aplikasi pengesah anda untuk meneruskan log masuk.
        </p>
    </div>

    <form method="POST"
          action="{{ $mode === 'portal' ? route('user.2fa.verify') : route('auth.2fa.verify') }}"
          class="auth-form"
          x-data="{ submitting: false }"
          @submit="submitting = true">
        @csrf

        <div class="auth-field">
            <label for="code" class="auth-label">Kod pengesahan</label>
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
            <span x-show="!submitting">Sahkan &amp; Log Masuk</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <span class="auth-spinner"></span>
                Mengesahkan...
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
            Kembali ke log masuk
        </button>
    </form>
@endsection
