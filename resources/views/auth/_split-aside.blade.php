@php
    $isPortal = ($context ?? 'admin') === 'portal';
    $roleLabel = $isPortal ? 'Portal Pemohon' : 'Portal Pentadbir';
    $headline = $isPortal
        ? 'Urus permohonan bantuan zakat dengan lebih mudah.'
        : 'Urus kelayakan asnaf dengan lebih yakin.';
@endphp

<aside class="auth-visual" aria-hidden="true">
    <div class="auth-visual__inner">
        <a href="{{ url('/') }}" class="auth-visual__brand" tabindex="-1">
            <span class="auth-logo">
                <img src="{{ asset('images/MAIJ.PNG') }}" alt="">
            </span>
            <span class="auth-brand-copy">
                <span class="auth-wordmark">J-ZAKAT</span>
                <span class="auth-brand-sub">Majlis Agama Islam Johor</span>
            </span>
        </a>

        <div class="auth-visual__content">
            <span class="auth-eyebrow">
                <x-icon name="sparkles" :size="13" />
                {{ $roleLabel }} &middot; Sistem Rasmi MAIJ
            </span>

            <h1>
                {{ $headline }}
                <span class="auth-highlight">Teknologi untuk ummah.</span>
            </h1>

            <p class="auth-visual__lede">
                Platform bersepadu untuk pengurusan permohonan, pengiraan Had Kifayah dan
                analisis kelayakan asnaf dalam satu sistem yang telus.
            </p>

            <ul class="auth-points">
                <li>
                    <span class="auth-point-icon"><x-icon name="shield-check" :size="17" /></span>
                    <span>
                        <strong>Selamat &amp; disulitkan</strong>
                        <span>Data pemohon dilindungi dan akses dikawal.</span>
                    </span>
                </li>
                <li>
                    <span class="auth-point-icon"><x-icon name="sparkles" :size="17" /></span>
                    <span>
                        <strong>Penilaian pintar</strong>
                        <span>Had Kifayah dan analisis AI dalam satu aliran kerja.</span>
                    </span>
                </li>
                <li>
                    <span class="auth-point-icon"><x-icon name="activity" :size="17" /></span>
                    <span>
                        <strong>Telus &amp; boleh diaudit</strong>
                        <span>Setiap tindakan direkod untuk akauntabiliti.</span>
                    </span>
                </li>
            </ul>
        </div>

        <div class="auth-visual__footer">
            <span class="auth-pulse" aria-hidden="true"></span>
            Sistem rasmi Majlis Agama Islam Johor
        </div>
    </div>
</aside>
