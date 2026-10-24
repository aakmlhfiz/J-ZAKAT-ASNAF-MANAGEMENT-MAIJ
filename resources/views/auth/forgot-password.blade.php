@php $mode = $mode ?? 'admin'; @endphp
@extends('auth.centered')
@section('title', 'Lupa Kata Laluan — J-ZAKAT AI')

@section('content')
    <div class="mb-5">
        <h1 class="text-[22px] font-bold text-center">Lupa Kata Laluan</h1>
        <p class="text-[13px] mt-1.5 text-center opacity-90">Masukkan alamat emel anda. Kami akan menghantar pautan set semula.</p>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-[14px] mb-5 flex items-center gap-3" style="background-color: rgba(34,197,94,0.18); border: 1px solid rgba(34,197,94,0.35);">
            <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(34,197,94,0.25); color: #BBF7D0;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-[13px] font-medium flex-1">{{ session('status') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'portal' ? route('user.password.email') : route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[13px] font-medium mb-1.5">Emel</label>
            <input type="email" name="email" id="email" required placeholder="nama@maij.gov.my" autocomplete="email" value="{{ old('email') }}" class="input-base input-auth w-full px-4 @error('email') input-error @enderror">
            @error('email')<p class="text-[12px] mt-1.5" style="color: #FECACA;">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-auth w-full mt-2 shadow-md shadow-primary/20">
            <span>Hantar Pautan Set Semula</span>
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