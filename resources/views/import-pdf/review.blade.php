@extends('layouts.app', [
    'title' => __('imports.review.meta_title'),
    'subtitle' => __('imports.review.meta_subtitle'),
])

@php
    $processing = $processing ?? false;
    $error = $error ?? null;
    $confidence = $confidence ?? 0;
    $import = $import ?? null;
    $extracted = $extracted ?? [];

    $fieldDefs = [
        ['f' => 'nama', 'label' => __('imports.field_labels.nama'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'ic', 'label' => __('imports.field_labels.ic'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'ic_ketua_rumah', 'label' => __('imports.field_labels.ic_ketua_rumah'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'hubungan', 'label' => __('imports.field_labels.hubungan'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'status_perkahwinan', 'label' => __('imports.field_labels.status_perkahwinan'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'merokok', 'label' => __('imports.field_labels.merokok'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'kesihatan', 'label' => __('imports.field_labels.kesihatan'), 'type' => 'text', 'cat' => 'applicant'],
        ['f' => 'telefon', 'label' => __('imports.field_labels.telefon'), 'type' => 'text', 'cat' => 'contact'],
        ['f' => 'no_telefon_bimbit', 'label' => __('imports.field_labels.no_telefon_bimbit'), 'type' => 'text', 'cat' => 'contact'],
        ['f' => 'no_telefon_rumah', 'label' => __('imports.field_labels.no_telefon_rumah'), 'type' => 'text', 'cat' => 'contact'],
        ['f' => 'alamat', 'label' => __('imports.field_labels.alamat'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'daerah', 'label' => __('imports.field_labels.daerah'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'bandar', 'label' => __('imports.field_labels.bandar'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'poskod', 'label' => __('imports.field_labels.poskod'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'dun', 'label' => __('imports.field_labels.dun'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'parlimen', 'label' => __('imports.field_labels.parlimen'), 'type' => 'text', 'cat' => 'address'],
        ['f' => 'pendapatan', 'label' => __('imports.field_labels.pendapatan'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'bilangan_tanggungan', 'label' => __('imports.field_labels.bilangan_tanggungan'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'sewa', 'label' => __('imports.field_labels.sewa'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'utiliti', 'label' => __('imports.field_labels.utiliti'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'pengangkutan', 'label' => __('imports.field_labels.pengangkutan'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'perubatan', 'label' => __('imports.field_labels.perubatan'), 'type' => 'number', 'cat' => 'financial'],
        ['f' => 'sebab_memohon', 'label' => __('imports.field_labels.sebab_memohon'), 'type' => 'text', 'cat' => 'assistance_review'],
    ];
@endphp

@section('content')

<x-page-header :title="__('imports.review.title')" :subtitle="$import->original_filename ?? __('imports.preview.subtitle_default')" :eyebrow="__('imports.review.eyebrow')" icon="fa-magnifying-glass">
    <x-slot:actions>
        <x-app-button variant="outline" size="md" icon="fa-file-pdf" href="{{ route('import-pdf.file', $import->id) }}">{{ __('imports.preview.view_original') }}</x-app-button>
        <x-app-button variant="outline" size="md" icon="fa-plus" href="{{ route('import-pdf.index') }}">{{ __('imports.preview.upload_another') }}</x-app-button>
    </x-slot:actions>
</x-page-header>

@if($processing)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="reviewPoller('{{ route('import-pdf.status', $import->id) }}')" x-init="startPolling()">
        <div class="lg:col-span-2 text-center py-16">
            <div class="w-16 h-16 rounded-[12px] bg-primary-soft mx-auto flex items-center justify-center mb-5">
                <i class="fa-solid fa-spinner fa-spin-pulse text-primary text-[24px]"></i>
            </div>
            <p class="text-h3 text-text">{{ __('imports.review.processing_title') }}</p>
            <p class="text-body text-secondary mt-2">{{ __('imports.review.processing_text') }}</p>
            <p class="text-small text-tertiary mt-4">{{ __('imports.review.processing_auto') }}</p>
            <div x-show="stalled" x-cloak class="mt-6 p-4 rounded-[8px] bg-warning-soft/40 border border-warning/20 max-w-md mx-auto text-small">
                {{ __('imports.review.stalled_text') }} <a class="underline" href="{{ route('import-pdf.index') }}">{{ __('imports.review.upload_again_link') }}</a> {{ __('imports.review.stalled_suffix') }}
            </div>
        </div>
    </div>
@elseif($error)
    <div class="surface p-4 mb-6 flex items-center gap-3 animate-fade-in" style="background-color: var(--color-danger-soft);">
        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(255, 59, 48, 0.15); color: var(--color-danger);">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <p class="text-body text-text font-medium flex-1">{{ $error }}</p>
    </div>
    <x-app-button variant="outline" icon="fa-rotate" href="{{ route('import-pdf.index') }}">{{ __('imports.review.upload_again') }}</x-app-button>
@else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: original file + raw OCR text + extras --}}
        <div class="space-y-6">
            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.review.original_document')" icon="fa-file-pdf" />
                @php
                    $isImage = str_starts_with($import->mime_type ?? '', 'image/');
                @endphp
                @if($isImage)
                    <a href="{{ route('import-pdf.file', $import->id) }}" target="_blank">
                        <img src="{{ route('import-pdf.file', $import->id) }}" alt="{{ __('imports.review.original_document_alt') }}" class="w-full rounded-[12px] border border-divider">
                    </a>
                @else
                    <a href="{{ route('import-pdf.file', $import->id) }}" target="_blank" class="btn btn-outline w-full">
                        <i class="fa-solid fa-file-pdf mr-2"></i> {{ __('imports.review.open_pdf') }}
                    </a>
                @endif
            </x-app-card>

            <x-app-card class="p-6" :hover="false">
                <x-section-header :title="__('imports.review.raw_ocr_text')" icon="fa-scroll" />
                <div class="flex items-center gap-2 mb-2">
                    <button type="button" onclick="navigator.clipboard.writeText(@json($import->ocr_text ?? '')).then(() => { const b = this; b.classList.add('text-success'); setTimeout(() => b.classList.remove('text-success'), 1500); })"
                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-primary hover:bg-primary-soft px-2 py-1 rounded-[6px]">
                        <i class="fa-solid fa-copy text-[10px]"></i> {{ __('imports.review.copy_text') }}
                    </button>
                </div>
                <details>
                    <summary class="cursor-pointer text-small text-primary font-medium">{{ __('imports.review.toggle_text') }}</summary>
                    <pre class="mt-3 p-4 rounded-[10px] bg-muted text-[12px] text-text whitespace-pre-wrap max-h-96 overflow-auto">{{ $import->ocr_text ?? __('imports.review.no_text') }}</pre>
                </details>
            </x-app-card>

            <x-app-card class="p-6" :hover="false" elevated accent="primary">
                <x-section-header :title="__('imports.review.confidence_title')" icon="fa-bullseye" />
                <div class="text-center py-3">
                    <p class="text-[42px] font-extrabold text-text tracking-tight tabular-nums">{{ number_format($confidence, 0) }}<span class="text-tertiary text-h3">%</span></p>
                    <p class="text-small text-secondary mt-1">{{ __('imports.review.confidence_average') }}</p>
                </div>
                <div class="h-3 bg-muted rounded-pill overflow-hidden mt-3">
                    <div class="h-full rounded-pill transition-all duration-500 @if($confidence >= 90) bg-success @elseif($confidence >= 70) bg-warning @else bg-danger @endif" style="width: {{ max(3, $confidence) }}%"></div>
                </div>
            </x-app-card>
        </div>

        {{-- RIGHT: editable fields with confidence badges --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('import-pdf.commit', $import->id) }}">
                @csrf
                <x-app-card class="p-6" :hover="false">
                    <div class="flex items-center justify-between mb-5">
                        <x-section-header :title="__('imports.review.extracted_title')" :description="__('imports.review.extracted_description')" icon="fa-database" />
                        <div class="text-eyebrow text-tertiary">{{ __('imports.review.engine') }} {{ $import->ocr_engine ?? '—' }}</div>
                    </div>

                    @php $currentCat = ''; @endphp
                    @foreach($fieldDefs as $def)
                        @if($def['cat'] !== $currentCat)
                            @php $currentCat = $def['cat']; @endphp
                            <div class="flex items-center gap-2 py-2 px-2 bg-muted/50 rounded-t-[10px] -mx-3 -mt-3 mb-2">
                                <i class="fa-solid fa-circle-dot text-tertiary text-[11px]"></i>
                                <span class="text-eyebrow text-tertiary font-semibold">{{ __('imports.categories.'.$def['cat']) }}</span>
                            </div>
                        @endif
                        @php
                            $field = $extracted[$def['f']] ?? null;
                            $value = $field['value'] ?? '';
                            $conf = (int) ($field['confidence'] ?? 0);
                            $needsReview = ($field['needs_review'] ?? false) || $conf < 70;
                            $tone = $conf >= 85 ? 'success' : ($conf >= 60 ? 'warning' : 'danger');
                        @endphp
                        <div class="flex items-start gap-3 py-2 border-b border-divider/50">
                            <div class="flex-1">
                                <label class="block text-small font-medium text-text mb-1">
                                    {{ $def['label'] }}
                                    @if($needsReview)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-pill bg-warning-soft text-warning align-middle ml-1">
                                            <i class="fa-solid fa-circle-exclamation text-[8px]"></i> {{ __('imports.review.needs_check') }}
                                        </span>
                                    @endif
                                </label>
                                <input type="{{ $def['type'] }}" step="0.01" name="{{ $def['f'] }}"
                                    value="{{ $value }}"
                                    class="input-base w-full @if($needsReview) border-warning/40 @endif @error($def['f']) input-error @enderror"
                                    placeholder="—">
                            </div>
                            <span class="mt-6 inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-pill bg-{{ $tone }}-soft text-{{ $tone }}">
                                <i class="fa-solid fa-circle text-[6px]"></i> {{ $conf }}%
                            </span>
                        </div>
                    @endforeach

                    @php $members = $import->household_members_extracted ?? []; @endphp
                    @if(count($members) > 0)
                        <div class="flex items-center gap-2 py-2 px-2 bg-muted/50 rounded-t-[10px] -mx-3 mt-3 mb-2">
                            <i class="fa-solid fa-people-roof text-tertiary text-[11px]"></i>
                            <span class="text-eyebrow text-tertiary font-semibold">{{ __('imports.review.household_members', ['count' => count($members)]) }}</span>
                        </div>
                        <div x-data="membersEditor(@json($members))" class="space-y-2">
                            <template x-for="(m, i) in members" :key="i">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2 border border-divider/60 rounded-[10px] items-center">
                                    <input type="text" name="household_members[i][nama]" x-model="m.nama"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_name') }}">
                                    <input type="text" name="household_members[i][ic]" x-model="m.ic"
                                           class="input-base w-full text-sm font-mono" placeholder="{{ __('imports.review.placeholder_ic') }}">
                                    <input type="text" name="household_members[i][hubungan]" x-model="m.hubungan"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_relationship') }}">
                                    <input type="number" name="household_members[i][umur]" x-model="m.umur"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_age') }}" min="0" max="120">
                                    <input type="text" name="household_members[i][pekerjaan]" x-model="m.pekerjaan"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_occupation') }}">
                                    <input type="number" step="0.01" min="0" name="household_members[i][pendapatan_bulanan]" x-model="m.pendapatan_bulanan"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_income') }}">
                                    <input type="text" name="household_members[i][pendidikan]" x-model="m.pendidikan"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_education') }}">
                                    <input type="text" name="household_members[i][status_kesihatan]" x-model="m.status_kesihatan"
                                           class="input-base w-full text-sm" placeholder="{{ __('imports.review.placeholder_health') }}">
                                    <div class="md:col-span-4 flex justify-end">
                                        <button type="button" @click="members.splice(i, 1)"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-danger hover:bg-danger-soft px-2 py-1 rounded-[6px]">
                                            <i class="fa-solid fa-trash text-[10px]"></i> {{ __('imports.review.remove') }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="addRow()"
                                    class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-primary hover:bg-primary-soft px-2.5 py-1.5 rounded-[8px]">
                                <i class="fa-solid fa-circle-plus text-[11px]"></i> {{ __('imports.review.add_member') }}
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mt-4 soft-panel p-4 text-small text-danger">
                            <ul class="list-disc ml-4 space-y-1">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
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

                    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-divider">
                        <x-app-button type="submit" variant="primary" size="lg" icon="fa-check-circle">{{ __('imports.review.save') }}</x-app-button>
                        <x-app-button variant="ghost" size="lg" href="{{ route('import-pdf.index') }}">{{ __('imports.review.cancel') }}</x-app-button>
                    </div>
                </x-app-card>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('membersEditor', (initial) => ({
            members: (initial || []).map(m => ({
                nama: m.nama || '',
                ic: m.ic || '',
                hubungan: m.hubungan || '',
                umur: m.umur || '',
                pekerjaan: m.pekerjaan || '',
                pendapatan_bulanan: m.pendapatan_bulanan || '',
                pendidikan: m.pendidikan || '',
                status_kesihatan: m.status_kesihatan || '',
            })),
            addRow() {
                this.members.push({
                    nama: '', ic: '', hubungan: '', umur: '',
                    pekerjaan: '', pendapatan_bulanan: '', pendidikan: '', status_kesihatan: '',
                });
            },
        }));

        Alpine.data('reviewPoller', (url) => ({
            stalled: false,
            timer: null,
            url: url,
            startPolling() {
                this.timer = setInterval(() => {
                    fetch(this.url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(d => {
                            if (d.stalled) { this.stalled = true; }
                            if (['extracted', 'needs_review', 'committed', 'failed'].includes(d.status)) {
                                window.location.reload();
                            }
                        })
                        .catch(() => {});
                }, 1500);
            }
        }));
    });
</script>
@endpush
