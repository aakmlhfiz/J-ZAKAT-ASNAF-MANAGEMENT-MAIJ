@extends('portal.layouts.app')
@section('title', 'Pratinjau Imbas Borang — Portal Pemohon')
@section('page-title', 'Pratinjau Imbas Borang')

@section('content')
@php
    $processing = $processing ?? false;
    $error = $error ?? null;
    $committed = $committed ?? false;
    $confidence = $confidence ?? 0;
    $extracted = $extracted ?? [];
    $householdMembers = $householdMembers ?? [];
    $borangData = $borangData ?? [];
    $application = $application ?? null;

    $fieldDefs = [
        ['field' => 'nama', 'label' => 'Nama Pemohon', 'icon' => 'fa-user', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'ic', 'label' => 'No. Kad Pengenalan', 'icon' => 'fa-id-card', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'status_perkahwinan', 'label' => 'Status Perkahwinan', 'icon' => 'fa-ring', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'merokok', 'label' => 'Merokok', 'icon' => 'fa-smoking', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'telefon', 'label' => 'No. Telefon', 'icon' => 'fa-phone', 'type' => 'text', 'category' => 'Maklumat Hubungan'],
        ['field' => 'no_telefon_bimbit', 'label' => 'No. Telefon Bimbit', 'icon' => 'fa-mobile-screen', 'type' => 'text', 'category' => 'Maklumat Hubungan'],
        ['field' => 'no_telefon_rumah', 'label' => 'No. Telefon Rumah', 'icon' => 'fa-phone', 'type' => 'text', 'category' => 'Maklumat Hubungan'],
        ['field' => 'alamat', 'label' => 'Alamat', 'icon' => 'fa-location-dot', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'daerah', 'label' => 'Daerah', 'icon' => 'fa-map', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'bandar', 'label' => 'Bandar', 'icon' => 'fa-city', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'poskod', 'label' => 'Poskod', 'icon' => 'fa-hashtag', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'dun', 'label' => 'DUN', 'icon' => 'fa-landmark', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'parlimen', 'label' => 'Parlimen', 'icon' => 'fa-landmark', 'type' => 'text', 'category' => 'Maklumat Alamat'],
        ['field' => 'pendapatan', 'label' => 'Pendapatan Bulanan (RM)', 'icon' => 'fa-money-bill', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'bilangan_tanggungan', 'label' => 'Bilangan Tanggungan', 'icon' => 'fa-people-group', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'sewa', 'label' => 'Sewa Rumah (RM)', 'icon' => 'fa-house', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'utiliti', 'label' => 'Bil Utiliti (RM)', 'icon' => 'fa-bolt', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'pengangkutan', 'label' => 'Pengangkutan (RM)', 'icon' => 'fa-car-side', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'perubatan', 'label' => 'Perubatan (RM)', 'icon' => 'fa-briefcase-medical', 'type' => 'number', 'category' => 'Maklumat Kewangan'],
        ['field' => 'bantuan_sara_hidup', 'label' => 'Bantuan Sara Hidup', 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'bantuan_perubatan', 'label' => 'Bantuan Perubatan', 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'bantuan_berdikari', 'label' => 'Bantuan Berdikari', 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'bantuan_perumahan', 'label' => 'Bantuan Perumahan', 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'menjelaskan_hutang', 'label' => 'Menjelaskan Hutang', 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'perubatan_jenis_rawatan', 'label' => 'Jenis Rawatan Perubatan', 'icon' => 'fa-briefcase-medical', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'perubatan_jenis_penyakit', 'label' => 'Jenis Penyakit', 'icon' => 'fa-virus', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'perubatan_kos_rawatan', 'label' => 'Kos Rawatan (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'jenis_bantuan_perumahan', 'label' => 'Bantuan Perumahan (Bina Baru/Baik Pulih)', 'icon' => 'fa-house-medical', 'type' => 'text', 'category' => 'Borang MAIJ §5D'],
        ['field' => 'status_tanah', 'label' => 'Status Tanah', 'icon' => 'fa-vector-square', 'type' => 'text', 'category' => 'Borang MAIJ §5D'],
        ['field' => 'jumlah_hutang', 'label' => 'Jumlah Hutang (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5E'],
        ['field' => 'harta_rumah', 'label' => 'Harta: Rumah', 'icon' => 'fa-house', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'harta_kereta', 'label' => 'Harta: Kereta', 'icon' => 'fa-car', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'harta_motosikal', 'label' => 'Harta: Motosikal', 'icon' => 'fa-motorcycle', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'pendapatan_diri', 'label' => 'Pendapatan: Diri (RM)', 'icon' => 'fa-user', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_pencen_perkeso', 'label' => 'Pendapatan: Pencen/PERKESO (RM)', 'icon' => 'fa-hand-holding-usd', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_makan_minum', 'label' => 'Perbelanjaan: Makan/Minum (RM)', 'icon' => 'fa-utensils', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_sewa_ansuran', 'label' => 'Perbelanjaan: Sewa/Ansuran (RM)', 'icon' => 'fa-house-crack', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
    ];

    $groupedFields = [];
    foreach ($fieldDefs as $def) {
        $groupedFields[$def['category']][] = $def;
    }

    $valueFor = function ($field) use ($extracted) {
        $entry = $extracted[$field] ?? null;
        if (!$entry) return null;
        return $entry['value'] ?? null;
    };

    $confidenceFor = function ($field) use ($extracted) {
        $entry = $extracted[$field] ?? null;
        if (!$entry) return 0;
        return (int) ($entry['confidence'] ?? 0);
    };
@endphp

<div class="max-w-4xl">
    <div class="mb-5 flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h1 class="text-title text-text">Pratinjau Imbas Borang</h1>
            <p class="text-small text-secondary mt-1">{{ $import->original_filename }}</p>
        </div>
        <a href="{{ route('user.applications.scan.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left text-[12px]"></i>
            <span>Imbas Lain</span>
        </a>
    </div>

    @if($error)
        <x-app-card class="p-5 mb-5" :hover="false">
            <div class="flex items-start gap-3" style="color: var(--color-danger);">
                <i class="fa-solid fa-circle-exclamation text-[18px] mt-0.5"></i>
                <div>
                    <p class="text-body font-semibold">Imbas Gagal</p>
                    <p class="text-small text-secondary mt-1">{{ $error }}</p>
                </div>
            </div>
        </x-app-card>
    @endif

    @if($processing)
        <x-app-card class="p-8 text-center" :hover="false" x-data="scanPoller('{{ route('user.applications.scan.status', $import->id) }}')" x-init="startPolling()">
            <div class="w-16 h-16 mx-auto rounded-full bg-primary-soft flex items-center justify-center mb-4">
                <i class="fa-solid fa-spinner fa-spin text-primary text-[24px]"></i>
            </div>
            <h2 class="text-body font-semibold text-text">AI Sedang Mengekstrak Borang...</h2>
            <p class="text-small text-secondary mt-2">Sila tunggu sebentar. Proses ini mengambil masa 10-30 saat.</p>
            <div class="mt-6 max-w-xs mx-auto h-2 bg-muted rounded-pill overflow-hidden">
                <div class="h-full bg-primary transition-all duration-300" :style="`width: ${progress}%`"></div>
            </div>

            <div x-show="elapsed >= 20" x-cloak class="mt-8 p-5 rounded-[16px] bg-warning-soft/40 border border-warning/20 max-w-md mx-auto">
                <p class="text-body font-semibold text-text">Pemprosesan mengambil masa lama.</p>
                <p class="text-small text-secondary mt-1">Jika ia tidak selesai, pastikan enjin OCR (Tesseract) dipasang dan pekerja queue sedang berjalan:
                    <code class="font-mono">php artisan queue:work</code></p>
                <form method="POST" action="{{ route('user.applications.scan.reprocess', $import->id) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-rotate text-[13px]"></i>
                        <span>Cuba Semula</span>
                    </button>
                </form>
            </div>
        </x-app-card>
    @elseif($committed)
        <x-app-card class="p-6 mb-5" :hover="false">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-[14px] bg-success-soft flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check-circle text-success text-[20px]"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-body font-semibold text-text">Borang Berjaya Dihantar</h2>
                    <p class="text-small text-secondary mt-1">
                        Permohonan anda telah dihantar untuk semakan pentadbir. Skor AI & status kelayakan telah dijana secara automatik.
                    </p>
                    @if($application)
                        <a href="{{ route('user.applications.show', $application->id) }}" class="btn btn-primary btn-sm mt-3">
                            <i class="fa-solid fa-arrow-right text-[12px]"></i>
                            <span>Lihat Permohonan</span>
                        </a>
                    @endif
                </div>
            </div>
        </x-app-card>
    @else
        <x-app-card class="p-5 mb-5" :hover="false">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-primary-soft flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass text-primary text-[14px]"></i>
                    </div>
                    <div>
                        <p class="text-small font-semibold text-text">Keyakinan Pengekstrakan AI</p>
                        <p class="text-eyebrow text-tertiary mt-0.5">{{ number_format($confidence, 1) }}% · Sila semak & sahkan maklumat di bawah</p>
                    </div>
                </div>
                @php
                    $tone = $confidence >= 80 ? 'success' : ($confidence >= 50 ? 'warning' : 'danger');
                    $label = $confidence >= 80 ? 'Tinggi' : ($confidence >= 50 ? 'Sederhana' : 'Rendah');
                @endphp
                <x-status-badge :status="$tone">{{ $label }}</x-status-badge>
            </div>
        </x-app-card>

        <form method="POST" action="{{ route('user.applications.scan.commit', $import->id) }}">
            @csrf

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-4">Maklumat Utama (Sila Sahkan)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Nama Penuh <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $valueFor('nama')) }}" required
                            class="input-base w-full @error('full_name') input-error @enderror">
                        @error('full_name') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">No. Kad Pengenalan <span class="text-danger">*</span></label>
                        <input type="text" name="ic_no" value="{{ old('ic_no', $valueFor('ic')) }}" required maxlength="14"
                            class="input-base w-full @error('ic_no') input-error @enderror">
                        @error('ic_no') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">No. Telefon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $valueFor('no_telefon_bimbit') ?? $valueFor('telefon')) }}" required
                            class="input-base w-full @error('phone') input-error @enderror">
                        @error('phone') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">E-mel <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="input-base w-full @error('email') input-error @enderror">
                        @error('email') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Bilangan Ahli Rumah <span class="text-danger">*</span></label>
                        <input type="number" name="household_count" value="{{ old('household_count', $valueFor('bilangan_tanggungan') ?: 1) }}" required min="1" max="20"
                            class="input-base w-full @error('household_count') input-error @enderror">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pendapatan Bulanan (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_income" value="{{ old('monthly_income', $valueFor('pendapatan')) }}" required min="0" step="0.01"
                            class="input-base w-full @error('monthly_income') input-error @enderror">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pendapatan Pasangan (RM)</label>
                        <input type="number" name="spouse_income" value="{{ old('spouse_income', $valueFor('pendapatan_pasangan') ?: 0) }}" min="0" step="0.01"
                            class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Pekerjaan</label>
                        <input type="text" name="occupation" value="{{ old('occupation', $valueFor('pekerjaan_pemohon_jawatan')) }}"
                            class="input-base w-full">
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-4">Alamat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-small font-medium text-text mb-1.5">Alamat</label>
                        <input type="text" name="address" value="{{ old('address', $valueFor('alamat')) }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Daerah</label>
                        <input type="text" name="district" value="{{ old('district', $valueFor('daerah')) }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Negeri</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">Poskod</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $valueFor('poskod')) }}" maxlength="5" class="input-base w-full">
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-1">Maklumat Tambahan (Borang MAIJ)</h2>
                <p class="text-small text-secondary mb-4">Maklumat ini diekstrak secara automatik dari Borang Agihan Zakat. Sebahagian daripadanya akan disimpan dalam profil bantuan.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        'jumlah_sara_hidup' => 'Jumlah Sara Hidup (RM)',
                        'perubatan_jenis_penyakit' => 'Jenis Penyakit',
                        'perubatan_tempat_rawatan' => 'Tempat Rawatan',
                        'perubatan_kos_rawatan' => 'Kos Rawatan (RM)',
                        'jenis_bantuan_perumahan' => 'Bantuan Perumahan',
                        'status_tanah' => 'Status Tanah',
                        'jumlah_hutang' => 'Jumlah Hutang (RM)',
                        'sebab_memohon_bantuan' => 'Sebab Memohon',
                    ] as $field => $label)
                        @if($valueFor($field))
                            <div class="p-3 rounded-[12px] bg-muted/50">
                                <p class="text-eyebrow text-tertiary">{{ $label }}</p>
                                <p class="text-small text-text font-medium mt-1">{{ $valueFor($field) }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(empty(array_filter(array_map($valueFor, ['jumlah_sara_hidup', 'perubatan_jenis_penyakit', 'perubatan_kos_rawatan', 'jenis_bantuan_perumahan', 'jumlah_hutang']))))
                    <p class="text-small text-tertiary italic">Tiada maklumat tambahan diekstrak dari borang.</p>
                @endif
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="confirm" value="1" required class="mt-1">
                    <div class="text-small text-text">
                        <p class="font-semibold">Saya mengesahkan maklumat di atas adalah benar.</p>
                        <p class="text-secondary mt-1">Setelah disahkan, sistem akan menjana Skor AI (Had Kifayah) dan menghantar permohonan untuk semakan pentadbir.</p>
                    </div>
                </label>
                @error('confirm') <p class="text-small text-danger mt-2">{{ $message }}</p> @enderror
            </x-app-card>

            <div class="flex items-center gap-3 flex-wrap">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-paper-plane text-[14px]"></i>
                    <span>Sahkan & Hantar Permohonan</span>
                </button>
                <a href="{{ route('user.applications.scan.index') }}" class="btn btn-outline btn-lg">
                    <i class="fa-solid fa-xmark text-[13px]"></i>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scanPoller', (url) => ({
            progress: 0,
            elapsed: 0,
            interval: null,
            timer: null,
            url: url,

            startPolling() {
                this.progress = 10;
                this.timer = setInterval(() => { this.elapsed++; }, 1000);
                this.interval = setInterval(() => {
                    if (this.progress < 90) {
                        this.progress += 5;
                    }
                    fetch(this.url, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status !== 'processing') {
                                clearInterval(this.interval);
                                clearInterval(this.timer);
                                this.progress = 100;
                                setTimeout(() => window.location.reload(), 500);
                            }
                        })
                        .catch(() => clearInterval(this.interval));
                }, 2000);
            }
        }));
    });
</script>
@endpush
