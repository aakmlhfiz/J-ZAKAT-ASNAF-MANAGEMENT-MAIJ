@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', 'Pengesahan 2FA — J-ZAKAT AI')

@section('content')
    <div class="text-center mb-5">
        <div class="w-14 h-14 rounded-2xl bg-white/20 text-white flex items-center justify-center mx-auto mb-4" style="backdrop-filter: blur(8px);">
            <i class="fa-solid fa-shield-halved text-2xl"></i>
        </div>
        <h1 class="text-[22px] font-bold">Pengesahan 2FA</h1>
        <p class="text-[13px] mt-1.5 opacity-90">Sila masukkan kod 6 digit dari aplikasi pengesah anda untuk meneruskan log masuk.</p>
    </div>

    <form method="POST" action="{{ $mode === 'portal' ? route('user.2fa.verify') : route('auth.2fa.verify') }}">
        @csrf
        <div class="mb-5">
            <input type="text" name="code"
                   class="input-base input-auth text-center text-[24px] font-bold"
                   style="letter-spacing: 0.3em;"
                   maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                   placeholder="000000" autofocus required>
            @error('code')
                <p class="text-[12px] mt-2 flex items-center justify-center gap-1" style="color: #FECACA;">
                    <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth w-full">
            Sahkan &amp; Log Masuk
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-divider text-center">
        <form method="POST" action="{{ $mode === 'portal' ? route('user.2fa.cancel') : route('auth.2fa.cancel') }}">
            @csrf
            <button type="submit" class="text-[13px] opacity-90 hover:opacity-100 transition-fast bg-transparent border-none cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[11px] mr-1"></i>
                Kembali ke log masuk
            </button>
        </form>
    </div>
@endsection