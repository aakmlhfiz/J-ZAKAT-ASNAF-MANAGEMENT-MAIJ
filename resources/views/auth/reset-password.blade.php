@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', 'Set Semula Kata Laluan — J-ZAKAT AI')

@section('content')
    <div class="mb-5">
        <h1 class="text-[22px] font-bold text-center">Set Semula Kata Laluan</h1>
        <p class="text-[13px] mt-1.5 text-center opacity-90">Pilih kata laluan baharu untuk akaun anda.</p>
    </div>

    <form method="POST" action="{{ $mode === 'portal' ? route('user.password.update') : route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label for="password" class="block text-[13px] font-medium mb-1.5">Kata Laluan Baharu</label>
            <input type="password" name="password" id="password" required placeholder="Minimum 8 aksara" autocomplete="new-password" class="input-base input-auth w-full px-4 @error('password') input-error @enderror">
            <p class="text-[11px] opacity-75 mt-1.5">Minimum 8 aksara, campurkan huruf &amp; nombor.</p>
            @error('password')<p class="text-[12px] mt-1.5" style="color: #FECACA;">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-[13px] font-medium mb-1.5">Sahkan Kata Laluan Baharu</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ulang kata laluan" autocomplete="new-password" class="input-base input-auth w-full px-4">
        </div>

        <button type="submit" class="btn btn-primary btn-auth w-full mt-2 shadow-md shadow-primary/20">
            <span>Set Semula Kata Laluan</span>
        </button>
    </form>
@endsection

@section('footer')
    <p class="text-[13px]">
        <a href="{{ $mode === 'portal' ? route('user.login') : route('login') }}" class="font-semibold hover:underline">
            <i class="fa-solid fa-arrow-left text-[11px] mr-1"></i>Kembali ke Log Masuk
        </a>
    </p>
@endsection