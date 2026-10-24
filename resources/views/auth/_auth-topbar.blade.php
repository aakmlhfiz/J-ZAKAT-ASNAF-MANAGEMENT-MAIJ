<header class="auth-panel__top">
    <a href="{{ url('/') }}" class="auth-brand-mobile" aria-label="Kembali ke halaman utama">
        <span class="auth-logo">
            <img src="{{ asset('images/MAIJ.PNG') }}" alt="">
        </span>
        <span class="auth-brand-copy">
            <span class="auth-wordmark">J-ZAKAT</span>
            <span class="auth-brand-sub">Majlis Agama Islam Johor</span>
        </span>
    </a>

    <a href="{{ url('/') }}" class="auth-back">
        <x-icon name="arrow-left" :size="15" />
        Kembali ke utama
    </a>
</header>
