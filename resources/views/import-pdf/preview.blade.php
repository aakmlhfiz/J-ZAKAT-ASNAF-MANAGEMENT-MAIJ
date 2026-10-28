@extends('layouts.app', [
    'title' => __('imports.preview.meta_title'),
    'subtitle' => __('imports.preview.meta_subtitle'),
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
        ['field' => 'nama', 'label' => __('imports.field_labels.nama'), 'icon' => 'fa-user', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'ic', 'label' => __('imports.field_labels.ic'), 'icon' => 'fa-id-card', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'ic_ketua_rumah', 'label' => __('imports.field_labels.ic_ketua_rumah'), 'icon' => 'fa-id-card', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'hubungan', 'label' => __('imports.field_labels.hubungan'), 'icon' => 'fa-user-group', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'status_perkahwinan', 'label' => __('imports.field_labels.status_perkahwinan'), 'icon' => 'fa-ring', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'merokok', 'label' => __('imports.field_labels.merokok'), 'icon' => 'fa-smoking', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'kesihatan', 'label' => __('imports.field_labels.kesihatan'), 'icon' => 'fa-heart-pulse', 'type' => 'text', 'category' => 'applicant'],
        ['field' => 'telefon', 'label' => __('imports.field_labels.telefon'), 'icon' => 'fa-phone', 'type' => 'text', 'category' => 'contact'],
        ['field' => 'no_telefon_bimbit', 'label' => __('imports.field_labels.no_telefon_bimbit'), 'icon' => 'fa-mobile-screen', 'type' => 'text', 'category' => 'contact'],
        ['field' => 'no_telefon_rumah', 'label' => __('imports.field_labels.no_telefon_rumah'), 'icon' => 'fa-phone', 'type' => 'text', 'category' => 'contact'],
        ['field' => 'alamat', 'label' => __('imports.field_labels.alamat'), 'icon' => 'fa-location-dot', 'type' => 'text', 'category' => 'address'],
        ['field' => 'daerah', 'label' => __('imports.field_labels.daerah'), 'icon' => 'fa-map', 'type' => 'text', 'category' => 'address'],
        ['field' => 'bandar', 'label' => __('imports.field_labels.bandar'), 'icon' => 'fa-city', 'type' => 'text', 'category' => 'address'],
        ['field' => 'poskod', 'label' => __('imports.field_labels.poskod'), 'icon' => 'fa-hashtag', 'type' => 'text', 'category' => 'address'],
        ['field' => 'dun', 'label' => __('imports.field_labels.dun'), 'icon' => 'fa-landmark', 'type' => 'text', 'category' => 'address'],
        ['field' => 'parlimen', 'label' => __('imports.field_labels.parlimen'), 'icon' => 'fa-landmark', 'type' => 'text', 'category' => 'address'],
        ['field' => 'pendapatan', 'label' => __('imports.field_labels.pendapatan'), 'icon' => 'fa-money-bill', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'bilangan_tanggungan', 'label' => __('imports.field_labels.bilangan_tanggungan'), 'icon' => 'fa-people-group', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'sewa', 'label' => __('imports.field_labels.sewa'), 'icon' => 'fa-house', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'utiliti', 'label' => __('imports.field_labels.utiliti'), 'icon' => 'fa-bolt', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'pengangkutan', 'label' => __('imports.field_labels.pengangkutan'), 'icon' => 'fa-car-side', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'perubatan', 'label' => __('imports.field_labels.perubatan'), 'icon' => 'fa-briefcase-medical', 'type' => 'number', 'category' => 'financial'],
        ['field' => 'bantuan_sara_hidup', 'label' => __('imports.field_labels.bantuan_sara_hidup'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'bantuan_perubatan', 'label' => __('imports.field_labels.bantuan_perubatan'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'bantuan_berdikari', 'label' => __('imports.field_labels.bantuan_berdikari'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'bantuan_perumahan', 'label' => __('imports.field_labels.bantuan_perumahan'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'menjelaskan_hutang', 'label' => __('imports.field_labels.menjelaskan_hutang'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'jumlah_bantuan_diperlukan', 'label' => __('imports.field_labels.jumlah_bantuan_diperlukan'), 'icon' => 'fa-hand-holding-heart', 'type' => 'number', 'category' => 'assistance'],
        ['field' => 'sebab_memohon_bantuan', 'label' => __('imports.field_labels.sebab_memohon'), 'icon' => 'fa-circle-question', 'type' => 'text', 'category' => 'assistance'],
        ['field' => 'jumlah_sara_hidup', 'label' => __('imports.field_labels.jumlah_sara_hidup'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'maij_5a'],
        ['field' => 'no_akaun_bimb', 'label' => __('imports.field_labels.no_akaun_bimb'), 'icon' => 'fa-bank', 'type' => 'text', 'category' => 'maij_5a'],
        ['field' => 'perubatan_jenis_rawatan', 'label' => __('imports.field_labels.perubatan_jenis_rawatan'), 'icon' => 'fa-briefcase-medical', 'type' => 'text', 'category' => 'maij_5b'],
        ['field' => 'perubatan_jenis_penyakit', 'label' => __('imports.field_labels.perubatan_jenis_penyakit'), 'icon' => 'fa-virus', 'type' => 'text', 'category' => 'maij_5b'],
        ['field' => 'perubatan_tempat_rawatan', 'label' => __('imports.field_labels.perubatan_tempat_rawatan'), 'icon' => 'fa-hospital', 'type' => 'text', 'category' => 'maij_5b'],
        ['field' => 'perubatan_kos_rawatan', 'label' => __('imports.field_labels.perubatan_kos_rawatan'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'maij_5b'],
        ['field' => 'jenis_berdikari', 'label' => __('imports.field_labels.jenis_berdikari'), 'icon' => 'fa-screwdriver-wrench', 'type' => 'text', 'category' => 'maij_5c'],
        ['field' => 'berdikari_jenis_peralatan', 'label' => __('imports.field_labels.berdikari_jenis_peralatan'), 'icon' => 'fa-toolbox', 'type' => 'text', 'category' => 'maij_5c'],
        ['field' => 'berdikari_harga_peralatan', 'label' => __('imports.field_labels.berdikari_harga_peralatan'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'maij_5c'],
        ['field' => 'jenis_bantuan_perumahan', 'label' => __('imports.field_labels.jenis_bantuan_perumahan'), 'icon' => 'fa-house-medical', 'type' => 'text', 'category' => 'maij_5d'],
        ['field' => 'status_tanah', 'label' => __('imports.field_labels.status_tanah'), 'icon' => 'fa-vector-square', 'type' => 'text', 'category' => 'maij_5d'],
        ['field' => 'sebab_berhutang', 'label' => __('imports.field_labels.sebab_berhutang'), 'icon' => 'fa-circle-question', 'type' => 'text', 'category' => 'maij_5e'],
        ['field' => 'jumlah_hutang', 'label' => __('imports.field_labels.jumlah_hutang'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => 'maij_5e'],
        ['field' => 'tempat_tinggal_sendiri', 'label' => __('imports.field_labels.tempat_tinggal_sendiri'), 'icon' => 'fa-house', 'type' => 'text', 'category' => 'maij_7'],
        ['field' => 'tempat_tinggal_menumpang', 'label' => __('imports.field_labels.tempat_tinggal_menumpang'), 'icon' => 'fa-people-roof', 'type' => 'text', 'category' => 'maij_7'],
        ['field' => 'tempat_tinggal_menyewe', 'label' => __('imports.field_labels.tempat_tinggal_menyewe'), 'icon' => 'fa-house-chimney', 'type' => 'text', 'category' => 'maij_7'],
        ['field' => 'harta_rumah', 'label' => __('imports.field_labels.harta_rumah'), 'icon' => 'fa-house', 'type' => 'text', 'category' => 'maij_8'],
        ['field' => 'harta_kereta', 'label' => __('imports.field_labels.harta_kereta'), 'icon' => 'fa-car', 'type' => 'text', 'category' => 'maij_8'],
        ['field' => 'harta_motosikal', 'label' => __('imports.field_labels.harta_motosikal'), 'icon' => 'fa-motorcycle', 'type' => 'text', 'category' => 'maij_8'],
        ['field' => 'pendapatan_diri', 'label' => __('imports.field_labels.pendapatan_diri'), 'icon' => 'fa-user', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'pendapatan_pasangan_ibubapa_penjaga', 'label' => __('imports.field_labels.pendapatan_pasangan_ibubapa_penjaga'), 'icon' => 'fa-people-roof', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'pendapatan_pencen_perkeso', 'label' => __('imports.field_labels.pendapatan_pencen_perkeso'), 'icon' => 'fa-hand-holding-usd', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'pendapatan_sumbangan_anak', 'label' => __('imports.field_labels.pendapatan_sumbangan_anak'), 'icon' => 'fa-child', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'pendapatan_bantuan_agensi', 'label' => __('imports.field_labels.pendapatan_bantuan_agensi'), 'icon' => 'fa-building', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'pendapatan_lain_lain', 'label' => __('imports.field_labels.pendapatan_lain_lain'), 'icon' => 'fa-coins', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'perbelanjaan_makan_minum', 'label' => __('imports.field_labels.perbelanjaan_makan_minum'), 'icon' => 'fa-utensils', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'perbelanjaan_bil_api_air', 'label' => __('imports.field_labels.perbelanjaan_bil_api_air'), 'icon' => 'fa-droplet', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'perbelanjaan_persekolahan', 'label' => __('imports.field_labels.perbelanjaan_persekolahan'), 'icon' => 'fa-school', 'type' => 'text', 'category' => 'maij_10'],
        ['field' => 'perbelanjaan_sewa_ansuran', 'label' => __('imports.field_labels.perbelanjaan_sewa_ansuran'), 'icon' => 'fa-house-crack', 'type' => 'text', 'category' => 'maij_10'],
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

<x-page-header :title="__('imports.preview.title')" :subtitle="$import->original_filename ?? __('imports.preview.subtitle_default')" :eyebrow="__('imports.preview.eyebrow')" icon="fa-magnifying-glass">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">{{ __('imports.preview.view_original') }}</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-plus" href="{{ route('import-pdf.index') }}">{{ __('imports.preview.upload_another') }}</x-app-button>
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
                <p class="text-h3 text-text">{{ __('imports.preview.processing_title') }}</p>
                <p class="text-body text-secondary mt-2">{{ __('imports.preview.processing_text') }}</p>
                <p class="text-small text-tertiary mt-4">{{ __('imports.preview.processing_auto') }} <span class="font-mono" x-text="elapsed"></span>s</p>

                <div x-show="elapsed >= 15" x-cloak class="mt-8 p-5 rounded-[16px] bg-warning-soft/40 border border-warning/20 max-w-md mx-auto">
                    <p class="text-body font-semibold text-text">{{ __('imports.preview.slow_title') }}</p>
                    <p class="text-small text-secondary mt-1">{{ __('imports.preview.slow_text') }}
                        <code class="font-mono">php artisan queue:work</code></p>
                    <form method="POST" action="{{ route('import-pdf.reprocess', $import->id) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-rotate text-[13px]"></i>
                            <span>{{ __('imports.pdf.retry') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div>
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.preview.file_info_title')" icon="fa-circle-info" />
                <dl class="space-y-3">
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_name') }}</dt>
                        <dd class="text-small text-text font-medium truncate">{{ $import->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_type') }}</dt>
                        <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_size') }}</dt>
                        <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_pages') }}</dt>
                        <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.uploaded_at') }}</dt>
                        <dd class="text-small text-text">{{ $import->created_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') }}</dd>
                    </div>
                </dl>
            </x-app-card>
        </div>
    </div>
@else
    <x-app-card class="p-5 mb-6" :hover="false">
        <x-stepper :steps="__('imports.steps.pdf')"
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
                            <p class="text-body text-text font-semibold">{{ __('imports.preview.duplicates_title') }}</p>
                            <p class="text-small text-secondary">{{ __('imports.preview.duplicates_count', ['count' => $dw['warning_count']]) }}</p>
                        </div>
                        <x-status-badge :status="$dw['high_severity_count'] > 0 ? 'danger' : 'warning'" size="sm" class="ml-auto">
                            {{ __('imports.preview.duplicates_critical', ['count' => $dw['high_severity_count']]) }}
                        </x-status-badge>
                    </div>
                    <div class="space-y-2">
                        @foreach($dw['warnings'] as $warning)
                            <div class="p-3 rounded-[12px] @if($warning['severity'] === 'high') bg-danger-soft border border-danger/20 @else bg-warning-soft/30 border border-warning/15 @endif">
                                <p class="text-small font-semibold text-text">{{ $warning['title'] }}</p>
                                <p class="text-[12px] text-secondary mt-0.5">{{ $warning['description'] }}</p>
                                @if(!empty($warning['details']['previous_assistance']))
                                    <div class="mt-2 space-y-1">
                                        <p class="text-eyebrow text-tertiary">{{ __('imports.preview.previous_assistance') }}</p>
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
                            <p class="text-body text-text font-semibold">{{ __('imports.preview.household_found') }}</p>
                            <p class="text-small text-secondary">{{ $hl['household_id'] }} — {{ $hl['head_of_household'] }}</p>
                        </div>
                        <x-status-badge status="success" size="sm" class="ml-auto">{{ __('imports.preview.household_members_count', ['count' => $hl['total_members']]) }}</x-status-badge>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">{{ __('imports.preview.household_total_assistance', ['amount' => number_format($hl['total_assistance_received'], 2)]) }}</span>
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">{{ __('imports.preview.household_monthly', ['amount' => number_format($hl['total_monthly_assistance'], 2)]) }}</span>
                        <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill">{{ __('imports.preview.household_one_off', ['amount' => number_format($hl['total_one_off_assistance'], 2)]) }}</span>
                    </div>
                    @if(!empty($hl['all_assistance']))
                        <div class="mt-2 space-y-1">
                            <p class="text-eyebrow text-tertiary mb-2">{{ __('imports.preview.previous_assistance') }}</p>
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
                    <x-section-header :title="__('imports.preview.extracted_title')" :description="__('imports.preview.extracted_description')" icon="fa-database" />
                    <button @click="showValidation = !showValidation" type="button" class="btn btn-ghost text-small">
                        <i class="fa-solid" :class="showValidation ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span x-text="showValidation ? @js(__('imports.preview.hide_ai')) : @js(__('imports.preview.show_ai'))"></span>
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
                                        @case('applicant') fa-user @break
                                        @case('contact') fa-phone @break
                                        @case('address') fa-location-dot @break
                                        @case('financial') fa-money-bill @break
                                        @case('assistance') fa-hand-holding-heart @break
                                        @default fa-circle-dot
                                    @endswitch text-tertiary text-[11px]"></i>
                                    <span class="text-eyebrow text-tertiary font-semibold">{{ __('imports.categories.'.$currentCategory) }}</span>
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
                                {{ __('imports.preview.household_members', ['count' => count($hm)]) }}
                            </h4>
                            <div class="overflow-x-auto">
                                <table class="app-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('imports.preview.col_name') }}</th>
                                            <th>{{ __('imports.preview.col_ic') }}</th>
                                            <th>{{ __('imports.preview.col_relationship') }}</th>
                                            <th>{{ __('imports.preview.col_age') }}</th>
                                            <th>{{ __('imports.preview.col_confidence') }}</th>
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
                        <x-app-button type="submit" variant="primary" size="lg" icon="fa-check-circle">{{ __('imports.preview.save') }}</x-app-button>
                        @if($dw && $dw['has_warnings'])
                            <x-app-button type="submit" variant="warning" size="lg" icon="fa-triangle-exclamation" name="force_import" value="1">{{ __('imports.preview.force_save') }}</x-app-button>
                        @endif
                        <x-app-button variant="ghost" size="lg" href="{{ route('import-pdf.index') }}">{{ __('imports.preview.cancel') }}</x-app-button>
                    </form>

                    <form method="POST" action="{{ route('import-pdf.destroy', $import->id) }}" class="ml-auto" onsubmit="return confirm(@js(__('imports.preview.confirm_delete')))">
                        @csrf
                        @method('DELETE')
                        <x-app-button type="submit" variant="ghost" size="sm" icon="fa-trash" class="text-danger">{{ __('imports.preview.remove') }}</x-app-button>
                    </form>
                    </div>
            </x-app-card>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('imports.preview.file_info_title')" icon="fa-circle-info" />
                <dl class="space-y-3">
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_name') }}</dt>
                        <dd class="text-small text-text font-medium truncate">{{ $import->original_filename }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_type') }}</dt>
                        <dd class="text-small text-text">{{ strtoupper($import->mime_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_size') }}</dt>
                        <dd class="text-small text-text">{{ number_format($import->file_size / 1024, 1) }} KB</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.file_pages') }}</dt>
                        <dd class="text-small text-text">{{ $import->page_count ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.ocr_engine') }}</dt>
                        <dd class="text-small text-text">{{ $import->ocr_engine ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.status') }}</dt>
                        <dd class="text-small text-text">
                            <x-status-badge :status="@match($import->status) {'extracted' => 'success', 'needs_review' => 'warning', 'committed' => 'success', 'failed' => 'danger', default => 'warning' }" size="sm">
                                {{ match($import->status) {
                                    'extracted' => __('imports.status.extracted'),
                                    'needs_review' => __('imports.status.needs_review'),
                                    'committed' => __('imports.status.committed'),
                                    'failed' => __('imports.status.failed'),
                                    'processing' => __('imports.status.processing'),
                                    default => $import->status,
                                } }}
                            </x-status-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-eyebrow text-tertiary">{{ __('imports.preview.processed_at') }}</dt>
                        <dd class="text-small text-text">{{ $import->ocr_completed_at ? $import->ocr_completed_at->locale(app()->getLocale())->translatedFormat('d M Y, h:i A') : '—' }}</dd>
                    </div>
                </dl>
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated accent="primary">
                <x-section-header :title="__('imports.preview.confidence_title')" icon="fa-bullseye" />
                <div class="text-center py-3">
                    <p class="text-[42px] font-extrabold text-text tracking-tight tabular-nums">{{ $confidence }}<span class="text-tertiary text-h3">%</span></p>
                    <p class="text-small text-secondary mt-1">{{ __('imports.preview.confidence_average') }}</p>
                </div>
                <div class="h-3 bg-muted rounded-pill overflow-hidden mt-3">
                    <div class="h-full rounded-pill transition-all duration-500 @if($confidence >= 90) bg-success @elseif($confidence >= 70) bg-warning @else bg-danger @endif" style="width: {{ max(3, $confidence) }}%"></div>
                </div>
                <div class="mt-4 space-y-1.5">
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">{{ __('imports.preview.confidence_high') }}</span>
                        <span class="text-success font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) >= 90)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">{{ __('imports.preview.confidence_medium') }}</span>
                        <span class="text-warning font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) >= 70 && ($e['confidence'] ?? 0) < 90)->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-tertiary">{{ __('imports.preview.confidence_low') }}</span>
                        <span class="text-danger font-semibold tabular-nums">{{ collect($extracted)->filter(fn($e) => ($e['confidence'] ?? 0) < 70 && ($e['confidence'] ?? 0) > 0)->count() }}</span>
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated>
                <x-section-header :title="__('imports.preview.labels_title')" icon="fa-tags" />
                @php $matchedLabels = $import->matched_labels ?? []; @endphp
                @if(empty($matchedLabels))
                    <p class="text-small text-secondary">{{ __('imports.preview.no_labels') }}</p>
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
