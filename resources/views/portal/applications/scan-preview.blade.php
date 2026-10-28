@extends('portal.layouts.app')
@section('title', __('ocr.preview.meta_title'))
@section('page-title', __('ocr.preview.page_title'))

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
        ['field' => 'nama', 'label' => __('imports.field_labels.nama'), 'icon' => 'fa-user', 'type' => 'text', 'category' => __('imports.categories.applicant')],
        ['field' => 'ic', 'label' => __('imports.field_labels.ic'), 'icon' => 'fa-id-card', 'type' => 'text', 'category' => __('imports.categories.applicant')],
        ['field' => 'status_perkahwinan', 'label' => __('imports.field_labels.status_perkahwinan'), 'icon' => 'fa-ring', 'type' => 'text', 'category' => __('imports.categories.applicant')],
        ['field' => 'merokok', 'label' => __('imports.field_labels.merokok'), 'icon' => 'fa-smoking', 'type' => 'text', 'category' => __('imports.categories.applicant')],
        ['field' => 'telefon', 'label' => __('imports.field_labels.telefon'), 'icon' => 'fa-phone', 'type' => 'text', 'category' => __('imports.categories.contact')],
        ['field' => 'no_telefon_bimbit', 'label' => __('imports.field_labels.no_telefon_bimbit'), 'icon' => 'fa-mobile-screen', 'type' => 'text', 'category' => __('imports.categories.contact')],
        ['field' => 'no_telefon_rumah', 'label' => __('imports.field_labels.no_telefon_rumah'), 'icon' => 'fa-phone', 'type' => 'text', 'category' => __('imports.categories.contact')],
        ['field' => 'alamat', 'label' => __('imports.field_labels.alamat'), 'icon' => 'fa-location-dot', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'daerah', 'label' => __('imports.field_labels.daerah'), 'icon' => 'fa-map', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'bandar', 'label' => __('imports.field_labels.bandar'), 'icon' => 'fa-city', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'poskod', 'label' => __('imports.field_labels.poskod'), 'icon' => 'fa-hashtag', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'dun', 'label' => __('imports.field_labels.dun'), 'icon' => 'fa-landmark', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'parlimen', 'label' => __('imports.field_labels.parlimen'), 'icon' => 'fa-landmark', 'type' => 'text', 'category' => __('imports.categories.address')],
        ['field' => 'pendapatan', 'label' => __('imports.field_labels.pendapatan'), 'icon' => 'fa-money-bill', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'bilangan_tanggungan', 'label' => __('imports.field_labels.bilangan_tanggungan'), 'icon' => 'fa-people-group', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'sewa', 'label' => __('imports.field_labels.sewa'), 'icon' => 'fa-house', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'utiliti', 'label' => __('imports.field_labels.utiliti'), 'icon' => 'fa-bolt', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'pengangkutan', 'label' => __('imports.field_labels.pengangkutan'), 'icon' => 'fa-car-side', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'perubatan', 'label' => __('imports.field_labels.perubatan'), 'icon' => 'fa-briefcase-medical', 'type' => 'number', 'category' => __('imports.categories.financial')],
        ['field' => 'bantuan_sara_hidup', 'label' => __('imports.field_labels.bantuan_sara_hidup'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => __('imports.categories.assistance')],
        ['field' => 'bantuan_perubatan', 'label' => __('imports.field_labels.bantuan_perubatan'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => __('imports.categories.assistance')],
        ['field' => 'bantuan_berdikari', 'label' => __('imports.field_labels.bantuan_berdikari'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => __('imports.categories.assistance')],
        ['field' => 'bantuan_perumahan', 'label' => __('imports.field_labels.bantuan_perumahan'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => __('imports.categories.assistance')],
        ['field' => 'menjelaskan_hutang', 'label' => __('imports.field_labels.menjelaskan_hutang'), 'icon' => 'fa-hand-holding-heart', 'type' => 'text', 'category' => __('imports.categories.assistance')],
        ['field' => 'perubatan_jenis_rawatan', 'label' => __('imports.field_labels.perubatan_jenis_rawatan'), 'icon' => 'fa-briefcase-medical', 'type' => 'text', 'category' => __('imports.categories.maij_5b')],
        ['field' => 'perubatan_jenis_penyakit', 'label' => __('imports.field_labels.perubatan_jenis_penyakit'), 'icon' => 'fa-virus', 'type' => 'text', 'category' => __('imports.categories.maij_5b')],
        ['field' => 'perubatan_kos_rawatan', 'label' => __('imports.field_labels.perubatan_kos_rawatan'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => __('imports.categories.maij_5b')],
        ['field' => 'jenis_bantuan_perumahan', 'label' => __('imports.field_labels.jenis_bantuan_perumahan'), 'icon' => 'fa-house-medical', 'type' => 'text', 'category' => __('imports.categories.maij_5d')],
        ['field' => 'status_tanah', 'label' => __('imports.field_labels.status_tanah'), 'icon' => 'fa-vector-square', 'type' => 'text', 'category' => __('imports.categories.maij_5d')],
        ['field' => 'jumlah_hutang', 'label' => __('imports.field_labels.jumlah_hutang'), 'icon' => 'fa-money-bill', 'type' => 'text', 'category' => __('imports.categories.maij_5e')],
        ['field' => 'harta_rumah', 'label' => __('imports.field_labels.harta_rumah'), 'icon' => 'fa-house', 'type' => 'text', 'category' => __('imports.categories.maij_8')],
        ['field' => 'harta_kereta', 'label' => __('imports.field_labels.harta_kereta'), 'icon' => 'fa-car', 'type' => 'text', 'category' => __('imports.categories.maij_8')],
        ['field' => 'harta_motosikal', 'label' => __('imports.field_labels.harta_motosikal'), 'icon' => 'fa-motorcycle', 'type' => 'text', 'category' => __('imports.categories.maij_8')],
        ['field' => 'pendapatan_diri', 'label' => __('imports.field_labels.pendapatan_diri'), 'icon' => 'fa-user', 'type' => 'text', 'category' => __('imports.categories.maij_10')],
        ['field' => 'pendapatan_pencen_perkeso', 'label' => __('imports.field_labels.pendapatan_pencen_perkeso'), 'icon' => 'fa-hand-holding-usd', 'type' => 'text', 'category' => __('imports.categories.maij_10')],
        ['field' => 'perbelanjaan_makan_minum', 'label' => __('imports.field_labels.perbelanjaan_makan_minum'), 'icon' => 'fa-utensils', 'type' => 'text', 'category' => __('imports.categories.maij_10')],
        ['field' => 'perbelanjaan_sewa_ansuran', 'label' => __('imports.field_labels.perbelanjaan_sewa_ansuran'), 'icon' => 'fa-house-crack', 'type' => 'text', 'category' => __('imports.categories.maij_10')],
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
            <h1 class="text-title text-text">{{ __('ocr.preview.title') }}</h1>
            <p class="text-small text-secondary mt-1">{{ $import->original_filename }}</p>
        </div>
        <a href="{{ route('user.applications.scan.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left text-[12px]"></i>
            <span>{{ __('ocr.preview.scan_another') }}</span>
        </a>
    </div>

    @if($error)
        <x-app-card class="p-5 mb-5" :hover="false">
            <div class="flex items-start gap-3" style="color: var(--color-danger);">
                <i class="fa-solid fa-circle-exclamation text-[18px] mt-0.5"></i>
                <div>
                    <p class="text-body font-semibold">{{ __('ocr.preview.error_title') }}</p>
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
            <h2 class="text-body font-semibold text-text">{{ __('ocr.preview.processing_title') }}</h2>
            <p class="text-small text-secondary mt-2">{{ __('ocr.preview.processing_text') }}</p>
            <div class="mt-6 max-w-xs mx-auto h-2 bg-muted rounded-pill overflow-hidden">
                <div class="h-full bg-primary transition-all duration-300" :style="`width: ${progress}%`"></div>
            </div>

            <div x-show="elapsed >= 20" x-cloak class="mt-8 p-5 rounded-[16px] bg-warning-soft/40 border border-warning/20 max-w-md mx-auto">
                <p class="text-body font-semibold text-text">{{ __('ocr.preview.slow_title') }}</p>
                <p class="text-small text-secondary mt-1">{{ __('ocr.preview.slow_text') }}
                    <code class="font-mono">php artisan queue:work</code></p>
                <form method="POST" action="{{ route('user.applications.scan.reprocess', $import->id) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-rotate text-[13px]"></i>
                        <span>{{ __('ocr.preview.retry') }}</span>
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
                    <h2 class="text-body font-semibold text-text">{{ __('ocr.preview.committed_title') }}</h2>
                    <p class="text-small text-secondary mt-1">
                        {{ __('ocr.preview.committed_text') }}
                    </p>
                    @if($application)
                        <a href="{{ route('user.applications.show', $application->id) }}" class="btn btn-primary btn-sm mt-3">
                            <i class="fa-solid fa-arrow-right text-[12px]"></i>
                            <span>{{ __('ocr.preview.view_application') }}</span>
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
                        <p class="text-small font-semibold text-text">{{ __('ocr.preview.confidence_title') }}</p>
                        <p class="text-eyebrow text-tertiary mt-0.5">{{ __('ocr.preview.confidence_hint', ['confidence' => number_format($confidence, 1)]) }}</p>
                    </div>
                </div>
                @php
                    $tone = $confidence >= 80 ? 'success' : ($confidence >= 50 ? 'warning' : 'danger');
                    $label = $confidence >= 80 ? __('ocr.preview.confidence_high') : ($confidence >= 50 ? __('ocr.preview.confidence_medium') : __('ocr.preview.confidence_low'));
                @endphp
                <x-status-badge :status="$tone">{{ $label }}</x-status-badge>
            </div>
        </x-app-card>

        <form method="POST" action="{{ route('user.applications.scan.commit', $import->id) }}">
            @csrf

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-4">{{ __('ocr.preview.main_title') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.full_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $valueFor('nama')) }}" required
                            class="input-base w-full @error('full_name') input-error @enderror">
                        @error('full_name') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.ic_no') }} <span class="text-danger">*</span></label>
                        <input type="text" name="ic_no" value="{{ old('ic_no', $valueFor('ic')) }}" required maxlength="14"
                            class="input-base w-full @error('ic_no') input-error @enderror">
                        @error('ic_no') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.phone') }} <span class="text-danger">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $valueFor('no_telefon_bimbit') ?? $valueFor('telefon')) }}" required
                            class="input-base w-full @error('phone') input-error @enderror">
                        @error('phone') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.email') }} <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="input-base w-full @error('email') input-error @enderror">
                        @error('email') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.household_count') }} <span class="text-danger">*</span></label>
                        <input type="number" name="household_count" value="{{ old('household_count', $valueFor('bilangan_tanggungan') ?: 1) }}" required min="1" max="20"
                            class="input-base w-full @error('household_count') input-error @enderror">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.monthly_income') }} <span class="text-danger">*</span></label>
                        <input type="number" name="monthly_income" value="{{ old('monthly_income', $valueFor('pendapatan')) }}" required min="0" step="0.01"
                            class="input-base w-full @error('monthly_income') input-error @enderror">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.spouse_income') }}</label>
                        <input type="number" name="spouse_income" value="{{ old('spouse_income', $valueFor('pendapatan_pasangan') ?: 0) }}" min="0" step="0.01"
                            class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.occupation') }}</label>
                        <input type="text" name="occupation" value="{{ old('occupation', $valueFor('pekerjaan_pemohon_jawatan')) }}"
                            class="input-base w-full">
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-4">{{ __('ocr.preview.address_title') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $valueFor('alamat')) }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.district') }}</label>
                        <input type="text" name="district" value="{{ old('district', $valueFor('daerah')) }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.state') }}</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="input-base w-full">
                    </div>
                    <div>
                        <label class="block text-small font-medium text-text mb-1.5">{{ __('ocr.preview.postcode') }}</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $valueFor('poskod')) }}" maxlength="5" class="input-base w-full">
                    </div>
                </div>
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <h2 class="text-body font-semibold text-text mb-1">{{ __('ocr.preview.additional_title') }}</h2>
                <p class="text-small text-secondary mb-4">{{ __('ocr.preview.additional_description') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach(__('ocr.preview.additional_fields') as $field => $label)
                        @if($valueFor($field))
                            <div class="p-3 rounded-[12px] bg-muted/50">
                                <p class="text-eyebrow text-tertiary">{{ $label }}</p>
                                <p class="text-small text-text font-medium mt-1">{{ $valueFor($field) }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(empty(array_filter(array_map($valueFor, ['jumlah_sara_hidup', 'perubatan_jenis_penyakit', 'perubatan_kos_rawatan', 'jenis_bantuan_perumahan', 'jumlah_hutang']))))
                    <p class="text-small text-tertiary italic">{{ __('ocr.preview.no_additional') }}</p>
                @endif
            </x-app-card>

            <x-app-card class="p-6 mb-5" :hover="false">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="confirm" value="1" required class="mt-1">
                    <div class="text-small text-text">
                        <p class="font-semibold">{{ __('ocr.preview.confirm_title') }}</p>
                        <p class="text-secondary mt-1">{{ __('ocr.preview.confirm_text') }}</p>
                    </div>
                </label>
                @error('confirm') <p class="text-small text-danger mt-2">{{ $message }}</p> @enderror
            </x-app-card>

            <div class="flex items-center gap-3 flex-wrap">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-paper-plane text-[14px]"></i>
                    <span>{{ __('ocr.preview.submit') }}</span>
                </button>
                <a href="{{ route('user.applications.scan.index') }}" class="btn btn-outline btn-lg">
                    <i class="fa-solid fa-xmark text-[13px]"></i>
                    <span>{{ __('ocr.preview.cancel') }}</span>
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
