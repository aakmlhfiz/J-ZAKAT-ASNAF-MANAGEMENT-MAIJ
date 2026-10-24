<aside class="hidden md:flex flex-col items-stretch justify-center px-10 py-12 lg:px-14 lg:py-16 text-text-primary relative overflow-hidden">
    <div class="absolute inset-0 -z-10" style="background: radial-gradient(ellipse at top right, rgba(0,102,204,0.10) 0%, transparent 60%), radial-gradient(ellipse at bottom left, rgba(90,200,250,0.10) 0%, transparent 50%);"></div>
    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full -z-10" style="background: radial-gradient(circle, rgba(0,102,204,0.08) 0%, transparent 70%); filter: blur(40px);"></div>

    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-[14px] bg-primary flex items-center justify-center shadow-md overflow-hidden">
            <img src="{{ asset('images/MAIJ.PNG') }}" alt="MAIJ" class="w-full h-full object-contain">
        </div>
        <div>
            <p class="text-[15px] font-semibold text-text leading-none">J-ZAKAT</p>
            <p class="text-[11px] text-tertiary mt-1 leading-none">Smart Eligibility Platform</p>
        </div>
    </div>

    <h1 class="text-display text-text mt-10 leading-[1.1]">
        Penilaian Asnaf<br><span class="text-primary">yang lebih pantas</span><br>untuk MAIJ.
    </h1>
    <p class="text-body text-secondary mt-5 max-w-md leading-relaxed">
        Platform digital pintar untuk menguruskan permohonan bantuan zakat dengan metodologi MAIDAM dan analisis AI yang menyeluruh.
    </p>

    <ul class="mt-8 space-y-3 text-body text-text w-full max-w-md">
        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-success-soft text-success flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-[11px]"></i></span>Penilaian AI pintar &amp; keyakinan kelayakan</li>
        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-success-soft text-success flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-[11px]"></i></span>Pengiraan Had Kifayah MAIDAM</li>
        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-success-soft text-success flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-[11px]"></i></span>Pengurusan pemohon menyeluruh</li>
        <li class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-success-soft text-success flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-[11px]"></i></span>Analitik &amp; laporan masa nyata</li>
    </ul>

    <div class="mt-10 surface-elevated p-5 w-full max-w-md">
        <div class="flex items-center justify-between mb-3">
            <p class="text-caption text-tertiary">Trend Permohonan</p>
            <span class="text-[11px] text-success font-semibold flex items-center gap-1"><i class="fa-solid fa-arrow-trend-up text-[10px]"></i>+12.4%</span>
        </div>
        <canvas id="brandSparkline" class="w-full" style="height: 64px;"></canvas>
        <div class="grid grid-cols-3 gap-3 mt-4">
            <div>
                <p class="text-metric text-text">12K+</p>
                <p class="text-caption text-tertiary mt-1">Permohonan</p>
            </div>
            <div>
                <p class="text-metric text-success">95%</p>
                <p class="text-caption text-tertiary mt-1">Ketepatan AI</p>
            </div>
            <div>
                <p class="text-metric text-primary">24/7</p>
                <p class="text-caption text-tertiary mt-1">Akses Digital</p>
            </div>
        </div>
    </div>

    <p class="text-[11px] text-tertiary mt-8">© {{ date('Y') }} Majlis Agama Islam Johor</p>
</aside>

@once
@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('brandSparkline');
        if (!el || !window.Chart) return;
        const ctx = el.getContext('2d');
        const labels = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
        const data = [320, 412, 480, 510, 590, 640, 720, 810, 780, 860, 920, 1010];
        const grad = ctx.createLinearGradient(0, 0, 0, 64);
        grad.addColorStop(0, 'rgba(0, 102, 204, 0.30)');
        grad.addColorStop(1, 'rgba(0, 102, 204, 0)');
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ data, borderColor: '#3B82F6', backgroundColor: grad, borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
            },
        });
    });
</script>
@endpush
@endonce
