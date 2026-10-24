<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Dijumpai | J-ZAKAT</title>
    @vite(['resources/css/app.css'])
    <style>html{font-family:'Inter','Segoe UI',system-ui,sans-serif}</style>
</head>
<body class="min-h-screen bg-bg text-text antialiased flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-[24px] bg-primary-soft mx-auto flex items-center justify-center mb-6 ring-1 ring-primary/10">
            <span class="text-metric-xl text-primary tabular-nums">404</span>
        </div>
        <h1 class="text-title text-text mb-3">Halaman Tidak Dijumpai</h1>
        <p class="text-body text-secondary mb-8">Halaman yang anda cari mungkin telah dialihkan, dipadam, atau tidak wujud.</p>
        <a href="{{ url('/') }}" class="btn btn-primary btn-md">Kembali ke Utama</a>
    </div>
</body>
</html>
