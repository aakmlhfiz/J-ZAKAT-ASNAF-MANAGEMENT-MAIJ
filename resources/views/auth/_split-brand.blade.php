{{--
    Brand header for the split auth pages — mirrors the landing page nav
    (logo + J-ZAKAT wordmark + back-to-home link).
--}}
<div class="split-auth__brand">
    <a href="{{ url('/') }}" class="flex items-center gap-3 group min-w-0">
        <div class="w-11 h-11 rounded-[8px] bg-white flex items-center justify-center shadow-lg ring-1 ring-white/20 overflow-hidden shrink-0">
            <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain p-1">
        </div>
        <div class="leading-none min-w-0">
            <p class="text-[16px] font-bold text-white tracking-tight">J-ZAKAT</p>
            <p class="text-[10.5px] text-white/50 mt-1 tracking-wider uppercase truncate">Majlis Agama Islam Johor</p>
        </div>
    </a>
    <a href="{{ url('/') }}" class="hidden sm:inline-flex items-center gap-1.5 text-[12.5px] font-medium text-white/55 hover:text-white transition-fast shrink-0">
        <x-icon name="arrow-left" :size="15" /> Kembali ke Utama
    </a>
</div>
