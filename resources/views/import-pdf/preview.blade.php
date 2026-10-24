@extends('layouts.app', [
    'title' => 'Pratinjau Ekstrak PDF',
    'subtitle' => 'Semak dan edit data yang diekstrak sebelum disimpan',
])

@php
    $confidence = $confidence ?? 0;
    $processing = $processing ?? false;
    $error = $error ?? null;
    $importId = $import->id ?? 0;
    $hm = $householdMembers ?? [];
    $hl = $householdLookup ?? null;
    $dw = $duplicateWarnings ?? null;
    $vr = $validationResults ?? null;

    $fieldDefs = [
        ['field' => 'nama', 'label' => 'Nama Pemohon', 'icon' => 'fa-user', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'ic', 'label' => 'No. Kad Pengenalan', 'icon' => 'fa-id-card', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'ic_ketua_rumah', 'label' => 'IC Ketua Rumah', 'icon' => 'fa-id-card', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'hubungan', 'label' => 'Hubungan', 'icon' => 'fa-user-group', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'status_perkahwinan', 'label' => 'Status Perkahwinan', 'icon' => 'fa-ring', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'merokok', 'label' => 'Merokok', 'icon' => 'fa-smoking', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
        ['field' => 'kesihatan', 'label' => 'Status Kesihatan', 'icon' => 'fa-heart-pulse', 'type' => 'text', 'category' => 'Maklumat Pemohon'],
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
        ['field' => 'jumlah_bantuan_diperlukan', 'label' => 'Jumlah Bantuan Diperlukan (RM)', 'icon' => 'fa-hand-holding-heart', 'type' => 'number', 'category' => 'Jenis Bantuan'],
        ['field' => 'sebab_memohon_bantuan', 'label' => 'Sebab Memohon', 'icon' => 'fa-circle-question', 'type' => 'text', 'category' => 'Jenis Bantuan'],
        ['field' => 'jumlah_sara_hidup', 'label' => 'Jumlah Sara Hidup (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5A'],
        ['field' => 'no_akaun_bimb', 'label' => 'No. Akaun BIMB', 'icon' => 'fa-bank', 'type' => 'text', 'category' => 'Borang MAIJ §5A'],
        ['field' => 'perubatan_jenis_rawatan', 'label' => 'Jenis Rawatan (Dialisis/Pembedahan)', 'icon' => 'fa-briefcase-medical', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'perubatan_jenis_penyakit', 'label' => 'Jenis Penyakit', 'icon' => 'fa-virus', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'perubatan_tempat_rawatan', 'label' => 'Tempat Rawatan', 'icon' => 'fa-hospital', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'perubatan_kos_rawatan', 'label' => 'Kos Rawatan (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5B'],
        ['field' => 'jenis_berdikari', 'label' => 'Jenis Berdikari (Peralatan/Modal)', 'icon' => 'fa-screwdriver-wrench', 'type' => 'text', 'category' => 'Borang MAIJ §5C'],
        ['field' => 'berdikari_jenis_peralatan', 'label' => 'Jenis Peralatan', 'icon' => 'fa-toolbox', 'type' => 'text', 'category' => 'Borang MAIJ §5C'],
        ['field' => 'berdikari_harga_peralatan', 'label' => 'Harga Peralatan (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5C'],
        ['field' => 'jenis_bantuan_perumahan', 'label' => 'Bantuan Perumahan (Bina Baru/Baik Pulih)', 'icon' => 'fa-house-medical', 'type' => 'text', 'category' => 'Borang MAIJ §5D'],
        ['field' => 'status_tanah', 'label' => 'Status Tanah', 'icon' => 'fa-vector-square', 'type' => 'text', 'category' => 'Borang MAIJ §5D'],
        ['field' => 'sebab_berhutang', 'label' => 'Sebab Berhutang', 'icon' => 'fa-circle-question', 'type' => 'text', 'category' => 'Borang MAIJ §5E'],
        ['field' => 'jumlah_hutang', 'label' => 'Jumlah Hutang (RM)', 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'Borang MAIJ §5E'],
        ['field' => 'tempat_tinggal_sendiri', 'label' => 'Tempat Tinggal: Sendiri', 'icon' => 'fa-house', 'type' => 'text', 'category' => 'Borang MAIJ §7'],
        ['field' => 'tempat_tinggal_menumpang', 'label' => 'Tempat Tinggal: Menumpang', 'icon' => 'fa-people-roof', 'type' => 'text', 'category' => 'Borang MAIJ §7'],
        ['field' => 'tempat_tinggal_menyewe', 'label' => 'Tempat Tinggal: Menyewe', 'icon' => 'fa-house-chimney', 'type' => 'text', 'category' => 'Borang MAIJ §7'],
        ['field' => 'harta_rumah', 'label' => 'Harta: Rumah', 'icon' => 'fa-house', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'harta_kereta', 'label' => 'Harta: Kereta', 'icon' => 'fa-car', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'harta_motosikal', 'label' => 'Harta: Motosikal', 'icon' => 'fa-motorcycle', 'type' => 'text', 'category' => 'Borang MAIJ §8'],
        ['field' => 'pendapatan_diri', 'label' => 'Pendapatan: Diri (RM)', 'icon' => 'fa-user', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_pasangan_ibubapa_penjaga', 'label' => 'Pendapatan: Pasangan/IbuBapa (RM)', 'icon' => 'fa-people-roof', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_pencen_perkeso', 'label' => 'Pendapatan: Pencen/PERKESO (RM)', 'icon' => 'fa-hand-holding-usd', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_sumbangan_anak', 'label' => 'Pendapatan: Sumbangan Anak (RM)', 'icon' => 'fa-child', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_bantuan_agensi', 'label' => 'Pendapatan: Bantuan Agensi (RM)', 'icon' => 'fa-building', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'pendapatan_lain_lain', 'label' => 'Pendapatan: Lain-lain (RM)', 'icon' => 'fa-coins', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_makan_minum', 'label' => 'Perbelanjaan: Makan/Minum (RM)', 'icon' => 'fa-utensils', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_bil_api_air', 'label' => 'Perbelanjaan: Bil Api/Air (RM)', 'icon' => 'fa-droplet', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_persekolahan', 'label' => 'Perbelanjaan: Persekolahan (RM)', 'icon' => 'fa-school', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
        ['field' => 'perbelanjaan_sewa_ansuran', 'label' => 'Perbelanjaan: Sewa/Ansuran (RM)', 'icon' => 'fa-house-crack', 'type' => 'text', 'category' => 'Borang MAIJ §10'],
    ];

    $fieldDefs = array_values(array_filter($fieldDefs, fn($d) => isset($extracted[$d['field']]) || in_array($d['field'], ['nama', 'ic'])));
@endphp

@section('content')

@if(session('info'))
    <div class="surface p-4 mb-6 flex items-center gap-3 animate-fade-in" style="background-color: var(--color-info-soft);">
        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(0, 122, 255, 0.15); color: var(--color-info);">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <p class="text-body text-text font-medium flex-1">@php echo session('info') @endphp</p>
    </div>
@endif

@if($error)
    <div class="surface p-4 mb-6 flex items-center gap-3 animate-fade-in" style="background-color: var(--color-danger-soft);">
        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(255, 59, 48, 0.15); color: var(--color-danger);">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <p class="text-body text-text font-medium flex-1">{{ $error }}</p>
    </div>
@endif

<x-page-header title="Pratinjau Ekstrak" subtitle="{{ $import->original_filename ?? 'Data diekstrak dari dokumen' }}" eyebrow="Semakan & pengesahan" icon="fa-magnifying-glass">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">Lihat Fail Asal</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-plus" href="{{ route('import-pdf.index') }}">Muat Naik Lain</x-app-button>
    </x-slot:actions>
</x-page-header>

@if($processing)
    <div x-data="extractionPoller()" x-init="startPolling()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-import-pdf.extraction-progress :progress="50" status="processing" />
            <div class="mt-6 text-center py-12">
                <div class="w-16 h-16 rounded-[20px] bg-primary-soft mx-auto flex items-center justify-center mb-5">
                    <i class="fa-solid fa-spinner fa-spin-pulse text-primary text-[24px]"></i>
                </div>
                <p class="text-h3 text-text">Sedang Memproses</p>
                <p class="text-body text-secondary mt-2">AI sedang mengekstrak teks dan mengenal pasti medan dari dokumen anda.</p>
                <p class="text-small text-tertiary mt-4">Halaman ini akan dikemas kini secara automatik selepas pemprosesan selesai. <span class="font-mono" x-text="elapsed"></span>s</p>

                <div x-show="elapsed >= 15" x-cloak class="mt-8 p-5 rounded-[16px] bg-warning-soft/40 border border-warning/20 max-w-md mx-auto">
                    <p class="text-body font-semibold text-text">Pemprosesan mengambil masa lama.</p>
                    <p class="text-small text-secondary mt-1">Jika ia tidak selesai, semak status enjin OCR (Tesseract) atau pastikan pekerja queue sedang berjalan:
                        <code class="font-mono">php artisan queue:work</code></p>
                    <form method="POST" action="{{ route('import-pdf.reprocess', $import->id) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-rotate text-[13px]"></i>
                            <span>Cuba Semula</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div>
            <x-app-card class="p-6" :hover="false">
                <x-section-header title="Maklumat Fail" icon="fa-circle-info" />
                <dl class="space-y-3">
                    <div>
                        <dt class="text-eyebrow text-tertiary">Nama Fail</dt>
                        <dd class="text-small text-text font-medium truncate">{{ $import->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Jenis</dt>
                        <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Saiz</dt>
                        <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Halaman</dt>
                        <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Dimuat Naik</dt>
                        <dd class="text-small text-text">{{ $import->created_at->format('d M Y, h:i A') }}</dd>
                    </div>
                </dl>
            </x-app-card>
        </div>
    </div>
@else
    <x-app-card class="p-5 mb-6" :hover="false">
        <x-stepper :steps="['Muat Naik', 'OCR Pemprosesan', 'Pratinjau & Semakan', 'Pengesahan AI', 'Pengesahan', 'Diimport']"
            :current="$import->status === 'committed' ? 6 : ($import->status === 'needs_review' ? 3 : 3)" />
    </x-app-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ confidence: {{ $confidence }}, showValidation: false }">
        <div class="lg:col-span-2 space-y-6">
            <x-import-pdf.extraction-progress
                :progress="100"
                :status="$import->status"
                :confidence="$confidence"
                :error="$error" />

            @include('import-pdf._maij_review', ['ocrResult' => $ocrResult ?? null])

            @if($dw && $dw['has_warnings'])
                <x-app-card class="p-5" :hover="false" accent="warning">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-[12px] bg-warning-soft flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        </div>
                        <div>
                            <p class="text-body text-text font-semibold">Amaran Pendua Dikesan</p>
                            <p class="text-small text-secondary">{{ $dw['warning_count'] }} amaran ditemui</p>
                        </div>
                        <x-status-badge :status="$dw['high_severity_count'] > 0 ? 'danger' : 'warning'" size="sm" class="ml-auto">
                            {{ $dw['high_severity_count'] }} kritikal
                        </x-status-badge>
                    </div>
                    <div class="space-y-2">
                        @foreach($dw['warnings'] as $warning)
                            <div class="p-3 rounded-[12px] @if($warning['severity'] === 'high') bg-danger-soft border border-danger/20 @else bg-warning-soft/30 border border-warning/15 @endif">
                                <p class="text-small font-semibold text-text">{{ $warning['title'] }}</p>
                                <p class="text-[12px] text-secondary mt-0.5">{{ $warning['description'] }}</p>
                                @if(!empty($warning['details']['previous_assistance']))
                                    <div class="mt-2 space-y-1">
                                        <p class="text-eyebrow text-tertiary">Bantuan Terdahulu:</p>
                                        @foreach($warning['details']['previous_assistance'] as $pa)
                                            <span class="inline-block text-[11px] bg-muted text-text rounded-pill px-2 py-0.5 mr-1">
                                                {{ $pa['jenis_bantuan'] }} (RM{{ number_format($pa['amount'], 2) }})
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-app-card>
            @endif

            @if($hl && $hl['found'])
                <x-app-card class="p-5" :hover="false" accent="primary">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-[12px] bg-primary-soft flex items-center justify-center">
                            <i class="fa-solid fa-house-chimney-user text-primary"></i>
                        </div>
                        <div>
                            <p class="text-body text-text font-semibold">Isi Rumah Ditemui</p>
                            <p class="text-small text-secondary">{{ $hl['household_id'] }} — {{ $hl['head_of_household'] }}</p>
                        </div>
                        <x-status-badge status="success" size="sm" class="ml-auto">{{ $hl['total_members'] }} ahli</x-status-badge>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">Jumlah Bantuan: RM{{ number_format($hl['total_assistance_received'], 2) }}</span>
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">Bulanan: RM{{ number_format($hl['total_monthly_assistance'], 2) }}</span>
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">Sekali: RM{{ number_format($hl['total_one_off_assistance'], 2) }}</span>
                    </div>
                    @if(!empty($hl['all_assistance']))
                        <div class="mt-2 space-y-1">
                            <p class="text-eyebrow text-tertiary mb-2">Bantuan Terdahulu:</p>
                            @foreach(array_slice($hl['all_assistance'], 0, 5) as $ah)
                                <div class="flex items-center justify-between text-[12px] py-1 px-2 rounded-[6px] bg-muted">
                                    <span class="text-text">{{ $ah['jenis_bantuan'] }}</span>
                                    <span class="text-secondary">{{ $ah['approved_date'] ?? '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-app-card>
            @endif

            <x-app-card class="p-6" :hover="false">
                <div class="flex items-center justify-between mb-5">
                    <x-section-header title="Data Diekstrak" description="Semak dan edit medan yang dikesan oleh AI" icon="fa-database" />
                    <button @click="showValidation = !showValidation" type="button" class="btn btn-ghost text-small">
                        <i class="fa-solid" :class="showValidation ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span x-text="showValidation ? 'Sembunyi AI' : 'Tunjuk AI'"></span>
                    </button>
                </div>

                <form method="POST" action="{{ route('import-pdf.commit', $import->id) }}" id="preview-form">
                    @csrf

                    @php $currentCategory = ''; @endphp
                    <div class="divide-y divide-divider">
                        @foreach($fieldDefs as $def)
                            @php
                                $extracted = $extracted[$def['field']] ?? null;
                                $value = $extracted['value'] ?? '';
                                $conf = (int) ($extracted['confidence'] ?? 0);
                                $sourceLabel = $extracted['source_label'] ?? null;
                                $needsReview = $extracted['needs_review'] ?? false;
                                $displayValue = ($value === null || $value === '') ? '' : $value;

                                if ($def['category'] !== $currentCategory) {
                                    $currentCategory = $def['category'];
                                }
                            @endphp

                            @if($loop->first || $def['category'] !== ($fieldDefs[$loop->index - 1]['category'] ?? ''))
                                <div class="flex items-center gap-2 py-2 px-2 bg-muted/50 rounded-t-[10px] -mx-3 -mt-3">
                                    <i class="fa-solid @switch($currentCategory)
                                        @case('Maklumat Pemohon') fa-user @break
                                        @case('Maklumat Hubungan') fa-phone @break
                                        @case('Maklumat Alamat') fa-location-dot @break
                                        @case('Maklumat Kewangan') fa-money-bill @break
                                        @case('Jenis Bantuan') fa-hand-holding-heart @break
                                        @default fa-circle-dot
                                    @endswitch text-tertiary text-[11px]"></i>
                                    <span class="text-eyebrow text-tertiary font-semibold">{{ $currentCategory }}</span>
                                </div>
                            @endif

                            <x-import-pdf.field-row
                                :field="$def['field']"
                                :label="$def['label']"
                                :value="$displayValue"
                                :confidence="$conf"
                                :sourceLabel="$sourceLabel"
                                :needsReview="$needsReview"
                                :editable="true"
                                :type="$def['type']"
                                :icon="$def['icon']"
                            />
                        @endforeach
                    </div>

                    @if(!empty($hm))
                        <div class="mt-6 pt-4 border-t border-divider">
                            <h4 class="text-body font-semibold text-text mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-people-group text-primary text-[14px]"></i>
                                Ahli Isi Rumah Dikesan ({{ count($hm) }})
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="app-table">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>IC</th>
                                            <th>Hubungan</th>
                                            <th>Umur</th>
                                            <th>Keyakinan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hm as $member)
                                            <tr>
                                                <td class="text-small text-text font-medium">{{ $member['nama'] ?? '—' }}</td>
                                                <td class="text-small text-text tabular-nums">{{ $member['ic'] ?? '—' }}</td>
                                                <td class="text-small text-text">{{ $member['hubungan'] ?? '—' }}</td>
                                                <td class="text-small text-text">{{ $member['umur'] ?? '—' }}</td>
                                                <td>
                                                    @php $mc = $member['confidence'] ?? 50; @endphp
                                                    <span class="text-small @if($mc >= 80) text-success @elseif($mc >= 50) text-warning @else text-danger @endif font-semibold tabular-nums">{{ $mc }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mt-4 soft-panel p-4 text-small text-danger">
                            <ul class="list-disc ml-4 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-4 soft-panel p-4 text-small text-danger flex items-start gap-2">
                            <i class="fa-solid fa-circle-exclamation text-[13px] mt-0.5 shrink-0"></i>
                            <span>@php echo session('error') @endphp</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-divider sticky bottom-0 -mx-6 -mb-6 px-6 py-4 bg-card/60 backdrop-blur">
                        <x-app-button type="submit" variant="primary" size="lg" icon="fa-check-circle">Sah & Simpan</x-app-button>
                        @if($dw && $dw['has_warnings'])
                            <x-app-button type="submit" variant="warning" size="lg" icon="fa-triangle-exclamation" name="force_import" value="1">Paksa Simpan</x-app-button>
                        @endif
                        <x-app-button variant="ghost" size="lg" href="{{ route('import-pdf.index') }}">Batal</x-app-button>
                    </form>

                    <form method="POST" action="{{ route('import-pdf.destroy', $import->id) }}" class="ml-auto" onsubmit="return confirm('Padam rekod import ini?')">
                        @csrf
                        @method('DELETE')
                        <x-app-button type="submit" variant="ghost" size="sm" icon="fa-trash" class="text-danger">Buang</x-app-button>
                    </form>
                    </div>
            </x-app-card>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header title="Maklumat Fail" icon="fa-circle-info" />
                <dl class="space-y-3">
                    <div>
                        <dt class="text-eyebrow text-tertiary">Nama Fail</dt>
                        <dd class="text-small text-text font-medium truncate">{{ $import->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Jenis</dt>
                        <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Saiz</dt>
                        <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Halaman</dt>
                        <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Enjin OCR</dt>
                        <dd class="text-small text-text">{{ $import->ocr_engine ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Status</dt>
                        <dd class="text-small text-text">
                            <x-status-badge :status="@match($import->status) {'extracted' => 'success', 'needs_review' => 'warning', 'committed' => 'success', 'failed' => 'danger', default => 'warning' }" size="sm">
                                {{ match($import->status) {
                                    'extracted' => 'Diekstrak',
                                    'needs_review' => 'Perlu Semakan',
                                    'committed' => 'Disimpan',
                                    'failed' => 'Gagal',
                                    'processing' => 'Memproses',
                                    default => $import->status,
                                } }}
                            </x-status-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">Diproses</dt>
                        <dd class="text-small text-text">{{ $import->ocr_completed_at ? $import->ocr_completed_at->format('d M Y, h:i A') : '—' }}</dd>
                    </div>
                </dl>
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated accent="primary">
                <x-section-header title="Keyakinan AI" icon="fa-bullseye" />
                <div class="text-center py-3">
                    <p class="text-[42px] font-extrabold text-text tracking-tight tabular-nums">{{ $confidence }}<span class="text-tertiary text-h3">%</span></p>
                    <p class="text-small text-secondary mt-1">purata keyakinan keseluruhan</p>
                </div>
                <div class="h-3 bg-muted rounded-pill overflow-hidden mt-3">
                    <div class="h-full rounded-pill transition-all duration-500 @if($confidence >= 90) bg-success @elseif($confidence >= 70) bg-warning @else bg-danger @endif" style="width: {{ max(3, $confidence) }}%"></div>
                </div>
                <div class="mt-4 space-y-1.5">
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">Tinggi (>90%)</span>
                        <span class="text-success font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) >= 90)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">Sederhana (70-89%)</span>
                        <span class="text-warning font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) >= 70 && ($e['confidence'] ?? 0) < 90)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">Rendah (<70%)</span>
                        <span class="text-danger font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) < 70 && ($e['confidence'] ?? 0) > 0)->count() }}</span>
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header title="Label Dikesan" icon="fa-tags" />
                @php $matchedLabels = $import->matched_labels ?? []; @endphp
                @if(empty($matchedLabels))
                    <p class="text-small text-secondary">Tiada label dikesan.</p>
                @else
                    <ul class="space-y-1.5">
                        @foreach($matchedLabels as $match)
                            <li class="flex items-center justify-between text-[12px] py-1.5 px-2.5 rounded-[8px] bg-muted">
                                <span class="text-text font-medium">"{{ $match['label'] ?? $match }}"</span>
                                <span class="text-tertiary">→ {{ $match['field'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-app-card>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('extractionPoller', () => ({
            elapsed: 0,
            timer: null,
            pollInterval: null,

            startPolling() {
                this.timer = setInterval(() => { this.elapsed++; }, 1000);

                this.pollInterval = setInterval(() => {
                    fetch('{{ route('import-pdf.status', $import->id) }}')
                        .then(r => r.json())
                        .then(data => {
                            if (['extracted', 'needs_review', 'committed', 'failed'].includes(data.status)) {
                                window.location.reload();
                            }
                        })
                        .catch(() => {});
                }, 2000);
            }
        }));
    });
</script>
@endpush
