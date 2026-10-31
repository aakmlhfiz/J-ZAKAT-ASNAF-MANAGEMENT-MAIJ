@extends('layouts.app', [
    'title' => ($mode ?? null) === 'edit' ? __('asnaf.edit_title') : __('asnaf.title'),
    'subtitle' => ($mode ?? null) === 'edit' ? __('asnaf.edit_subtitle') : __('asnaf.subtitle'),
])

@section('content')
    <form
        method="POST"
        action="{{ ($mode ?? null) === 'edit' ? route('profilasnaf.update', $profil->ic) : route('profil-asnaf.store') }}"
        x-data="{
            pendapatan: {{ (float) (old('pendapatan', $profil->pendapatan ?? 0)) }},
            bilangan: {{ (int) (old('bilangan_tanggungan', $profil->bilangan_tanggungan ?? 0)) }},
            sewa: {{ (float) (old('sewa', $profil->sewa ?? 0)) }},
            utiliti: {{ (float) (old('utiliti', $profil->utiliti ?? 0)) }},
            perumahan: @js(old('perumahan', $profil->perumahan ?? 'sendiri')),
            get hadKifayah() { return 1200 + (this.bilangan * 350) + Number(this.sewa || 0) + Number(this.utiliti || 0); },
            get status() { return Number(this.pendapatan || 0) < this.hadKifayah ? 'Layak' : 'Tidak Layak'; },
            get statusLabel() { return this.status === 'Layak' ? @js(__('common.status.layak')) : @js(__('common.status.tidak_layak')); },
            get statusMessage() { return this.status === 'Layak' ? @js(__('asnaf.form.eligible_message')) : @js(__('asnaf.form.not_eligible_message')); },
            get statusVariant() { return this.status === 'Layak' ? 'success' : 'danger'; }
        }"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
    >
        @csrf
        @if(($mode ?? null) === 'edit') @method('PUT') @endif

        <div class="lg:col-span-2 space-y-6">
            <x-form-section title="{{ __('asnaf.form.personal_title') }}" description="{{ __('asnaf.form.personal_description') }}" icon="fa-user">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="{{ __('asnaf.form.name') }}" name="nama" required placeholder="{{ __('asnaf.form.name_placeholder') }}" :value="old('nama', $profil->nama ?? '')" icon="fa-user" />
                    <x-app-input label="{{ __('asnaf.form.ic') }}" name="ic" required placeholder="{{ __('asnaf.form.ic_placeholder') }}" :value="old('ic', $profil->ic ?? '')" :disabled="($mode ?? null) === 'edit'" icon="fa-id-card" />
                    <x-app-input label="{{ __('asnaf.form.phone') }}" name="telefon" type="tel" icon="fa-phone" placeholder="0123456789" :value="old('telefon', $profil->telefon ?? '')" />
                    <x-app-select label="{{ __('asnaf.form.relationship') }}" name="hubungan" placeholder="{{ __('asnaf.form.relationship_placeholder') }}">
                        @php $selectedHubungan = old('hubungan', $profil->hubungan ?? ''); @endphp
                        <option value="Ketua Keluarga" @selected($selectedHubungan === 'Ketua Keluarga')>{{ __('asnaf.form.relationship_head') }}</option>
                        <option value="Isteri" @selected($selectedHubungan === 'Isteri')>{{ __('asnaf.form.relationship_spouse') }}</option>
                        <option value="Anak" @selected($selectedHubungan === 'Anak')>{{ __('asnaf.form.relationship_child') }}</option>
                        <option value="Lain-lain" @selected($selectedHubungan === 'Lain-lain')>{{ __('asnaf.form.relationship_other') }}</option>
                    </x-app-select>
                </div>
            </x-form-section>

            <x-form-section title="{{ __('asnaf.form.household_title') }}" description="{{ __('asnaf.form.household_description') }}" icon="fa-people-roof">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="{{ __('asnaf.form.head_ic') }}" name="ic_ketua_rumah" placeholder="{{ __('asnaf.form.head_ic_placeholder') }}" :value="old('ic_ketua_rumah', $profil->ic_ketua_rumah ?? '')" icon="fa-id-card" />
                </div>
                <x-app-textarea label="{{ __('asnaf.form.address') }}" name="alamat" :rows="3" placeholder="{{ __('asnaf.form.address_placeholder') }}" :value="old('alamat', $profil->alamat ?? '')" icon="fa-location-dot" />
            </x-form-section>

            <x-form-section title="{{ __('asnaf.form.finance_title') }}" description="{{ __('asnaf.form.finance_description') }}" icon="fa-wallet" accent="primary">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="{{ __('asnaf.form.income') }}" name="pendapatan" type="number" step="0.01" placeholder="0.00" x-model="pendapatan" :value="old('pendapatan', $profil->pendapatan ?? '')" icon="fa-money-bill" />
                    <x-app-input label="{{ __('asnaf.form.dependents') }}" name="bilangan_tanggungan" type="number" placeholder="0" x-model="bilangan" :value="old('bilangan_tanggungan', $profil->bilangan_tanggungan ?? '')" icon="fa-people-group" />
                    <x-app-input label="{{ __('asnaf.form.rent') }}" name="sewa" type="number" step="0.01" placeholder="0.00" x-model="sewa" :value="old('sewa', $profil->sewa ?? '')" icon="fa-house" />
                    <x-app-input label="{{ __('asnaf.form.utilities') }}" name="utiliti" type="number" step="0.01" placeholder="0.00" x-model="utiliti" :value="old('utiliti', $profil->utiliti ?? '')" icon="fa-bolt" />
                </div>
                <div class="soft-panel p-4 flex items-center gap-3 mt-2">
                    <i class="fa-solid fa-circle-info text-primary text-[14px]"></i>
                    <p class="text-small text-text flex-1">{{ __('asnaf.form.kifayah_note') }}</p>
                </div>
            </x-form-section>

            <x-form-section title="{{ __('asnaf.form.household_kifayah_title') }}" description="{{ __('asnaf.form.household_kifayah_description') }}" icon="fa-people-group" accent="success">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <p class="col-span-full text-eyebrow text-tertiary mt-1 mb-1">{{ __('asnaf.form.adult_category') }}</p>
                    <x-app-input label="{{ __('asnaf.form.spouse') }}" name="n_isteri_suami" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_isteri_suami', $profil->n_isteri_suami ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.parents') }}" name="n_ibu_bapa" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_ibu_bapa', $profil->n_ibu_bapa ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.adult_unemployed') }}" name="n_dewasa_tidak_bekerja" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_dewasa_tidak_bekerja', $profil->n_dewasa_tidak_bekerja ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.adult_working_dependent') }}" name="n_dewasa_bekerja_tanggung" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_dewasa_bekerja_tanggung', $profil->n_dewasa_bekerja_tanggung ?? 0)" />
                    <p class="col-span-full text-eyebrow text-tertiary mt-3 mb-1">{{ __('asnaf.form.education_category') }}</p>
                    <x-app-input label="{{ __('asnaf.form.adult_tertiary') }}" name="n_ipt" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_ipt', $profil->n_ipt ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.teen_secondary') }}" name="n_remaja" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_remaja', $profil->n_remaja ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.child_primary') }}" name="n_kanak_rendah" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_kanak_rendah', $profil->n_kanak_rendah ?? 0)" />
                    <x-app-input label="{{ __('asnaf.form.child_kindergarten') }}" name="n_kanak_tadika" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_kanak_tadika', $profil->n_kanak_tadika ?? 0)" />
                    <p class="col-span-full text-eyebrow text-tertiary mt-3 mb-1">{{ __('asnaf.form.housing_category') }}</p>
                    <x-app-select label="{{ __('asnaf.form.housing_status') }}" name="perumahan" x-model="perumahan">
                        <option value="sendiri" @selected(old('perumahan', $profil->perumahan ?? 'sendiri') === 'sendiri')>{{ __('asnaf.form.housing_own') }}</option>
                        <option value="sewa" @selected(old('perumahan', $profil->perumahan ?? 'sendiri') === 'sewa')>{{ __('asnaf.form.housing_rent') }}</option>
                    </x-app-select>
                    <x-app-input label="{{ __('asnaf.form.transport') }}" name="pengangkutan_input" type="number" step="0.01" placeholder="0.00" :value="old('pengangkutan_input', $profil->pengangkutan_input ?? 0)" icon="fa-bus" />
                    <x-app-input label="{{ __('asnaf.form.medical') }}" name="perubatan_input" type="number" step="0.01" placeholder="0.00" :value="old('perubatan_input', $profil->perubatan_input ?? 0)" icon="fa-briefcase-medical" />
                </div>
            </x-form-section>

            <div class="flex items-center gap-3 sticky bottom-0 -mx-6 md:-mx-10 -mb-6 md:-mb-8 px-6 md:px-10 py-4 bg-bg border-t border-divider">
                <x-app-button type="submit" variant="primary" size="lg" icon="fa-check">{{ ($mode ?? null) === 'edit' ? __('asnaf.form.update') : __('asnaf.form.save') }}</x-app-button>
                <x-app-button variant="ghost" size="lg" href="{{ ($mode ?? null) === 'edit' ? route('viewprofile', $profil->ic) : route('viewprofile.index') }}">{{ __('asnaf.form.cancel') }}</x-app-button>
            </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <x-result-card label="{{ __('asnaf.form.preliminary') }}" subtext="{{ __('asnaf.form.preliminary_subtext') }}" icon="fa-calculator" status="primary" accent="primary">
                <div class="space-y-3 text-small">
                    <div class="flex items-center justify-between"><span class="text-secondary">{{ __('asnaf.form.base') }}</span><span class="font-medium text-text tabular-nums">RM 1,200.00</span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">{{ __('asnaf.form.dependents_short') }}</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + (Number(bilangan || 0) * 350).toFixed(2)"></span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">{{ __('asnaf.form.rent_short') }}</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + Number(sewa || 0).toFixed(2)"></span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">{{ __('asnaf.form.utilities_short') }}</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + Number(utiliti || 0).toFixed(2)"></span></div>
                    <div class="pt-3 border-t border-divider flex items-center justify-between">
                        <span class="text-text font-semibold">{{ __('asnaf.form.total') }}</span>
                        <span class="text-metric-sm text-text tabular-nums" x-text="'RM ' + hadKifayah.toFixed(2)"></span>
                    </div>
                </div>
            </x-result-card>

            <x-result-card label="{{ __('asnaf.form.eligibility') }}" subtext="{{ __('asnaf.form.eligibility_subtext') }}" icon="fa-shield-halved" x-bind:status="statusVariant" accent="success">
                <p class="text-body text-text" x-text="statusMessage"></p>
                <div class="mt-3 flex items-center gap-2">
                    <x-status-badge x-bind:status="statusVariant"><span x-text="statusLabel"></span></x-status-badge>
                </div>
            </x-result-card>
        </div>
    </form>
@endsection
