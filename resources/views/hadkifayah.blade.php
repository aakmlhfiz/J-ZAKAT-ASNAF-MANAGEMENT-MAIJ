@extends('layouts.app', [
    'title' => __('hadkifayah.title'),
    'subtitle' => __('hadkifayah.subtitle'),
])

@section('content')
    <x-page-header :title="__('hadkifayah.title')" :subtitle="__('hadkifayah.subtitle')" :eyebrow="__('hadkifayah.eyebrow')" icon="fa-calculator">
        <x-slot:actions>
            <x-app-button variant="outline" size="md" icon="fa-sliders" href="{{ route('hadkifayah.settings') }}">{{ __('hadkifayah.settings_button') }}</x-app-button>
        </x-slot:actions>
    </x-page-header>

    <div
        x-data="{
            pendapatan_pemohon: 0,
            pendapatan_pasangan: 0,
            pendapatan_anak: 0,
            pendapatan_lain: 0,
            n_isteri_suami: 0,
            n_ibu_bapa: 0,
            n_dewasa_tidak_bekerja: 0,
            n_dewasa_bekerja_tanggung: 0,
            n_ipt: 0,
            n_remaja: 0,
            n_kanak_rendah: 0,
            n_kanak_tadika: 0,
            perumahan: 'sendiri',
            utiliti_input: 0,
            pengangkutan_input: 0,
            perubatan_input: 0,
            result: null,
            loading: false,
            debounceTimer: null,

            get pendapatanTotal() {
                return Number(this.pendapatan_pemohon || 0)
                     + Number(this.pendapatan_pasangan || 0)
                     + Number(this.pendapatan_anak || 0)
                     + Number(this.pendapatan_lain || 0);
            },

            get totalTanggungan() {
                return Number(this.n_isteri_suami || 0)
                     + Number(this.n_ibu_bapa || 0)
                     + Number(this.n_dewasa_tidak_bekerja || 0)
                     + Number(this.n_dewasa_bekerja_tanggung || 0)
                     + Number(this.n_ipt || 0)
                     + Number(this.n_remaja || 0)
                     + Number(this.n_kanak_rendah || 0)
                     + Number(this.n_kanak_tadika || 0);
            },

            get totalPerbelanjaan() {
                return Number(this.utiliti_input || 0)
                     + Number(this.pengangkutan_input || 0)
                     + Number(this.perubatan_input || 0);
            },

            init() {
                this.$watch('pendapatan_pemohon', () => this.schedulePreview());
                this.$watch('pendapatan_pasangan', () => this.schedulePreview());
                this.$watch('pendapatan_anak', () => this.schedulePreview());
                this.$watch('pendapatan_lain', () => this.schedulePreview());
                this.$watch('n_isteri_suami', () => this.schedulePreview());
                this.$watch('n_ibu_bapa', () => this.schedulePreview());
                this.$watch('n_dewasa_tidak_bekerja', () => this.schedulePreview());
                this.$watch('n_dewasa_bekerja_tanggung', () => this.schedulePreview());
                this.$watch('n_ipt', () => this.schedulePreview());
                this.$watch('n_remaja', () => this.schedulePreview());
                this.$watch('n_kanak_rendah', () => this.schedulePreview());
                this.$watch('n_kanak_tadika', () => this.schedulePreview());
                this.$watch('perumahan', () => this.schedulePreview());
                this.$watch('utiliti_input', () => this.schedulePreview());
                this.$watch('pengangkutan_input', () => this.schedulePreview());
                this.$watch('perubatan_input', () => this.schedulePreview());
            },

            schedulePreview() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.runPreview(), 250);
            },

            async runPreview() {
                this.loading = true;
                try {
                    const payload = {
                        pendapatan_pemohon: this.pendapatan_pemohon,
                        pendapatan_pasangan: this.pendapatan_pasangan,
                        pendapatan_anak: this.pendapatan_anak,
                        pendapatan_lain: this.pendapatan_lain,
                        n_isteri_suami: this.n_isteri_suami,
                        n_ibu_bapa: this.n_ibu_bapa,
                        n_dewasa_tidak_bekerja: this.n_dewasa_tidak_bekerja,
                        n_dewasa_bekerja_tanggung: this.n_dewasa_bekerja_tanggung,
                        n_ipt: this.n_ipt,
                        n_remaja: this.n_remaja,
                        n_kanak_rendah: this.n_kanak_rendah,
                        n_kanak_tadika: this.n_kanak_tadika,
                        perumahan: this.perumahan,
                        utiliti_input: this.utiliti_input,
                        pengangkutan_input: this.pengangkutan_input,
                        perubatan_input: this.perubatan_input,
                    };
                    const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                    const res = await fetch('{{ route('hadkifayah.preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    if (res.ok) {
                        this.result = await res.json();
                    }
                } catch (e) {
                    console.error('Preview failed', e);
                } finally {
                    this.loading = false;
                }
            },

            get statusVariant() {
                if (!this.result) return 'primary';
                return this.result.status === 'Layak' ? 'success' : 'danger';
            },

            get statusIcon() {
                if (!this.result) return 'fa-shield-halved';
                return this.result.status === 'Layak' ? 'fa-circle-check' : 'fa-circle-xmark';
            },

            get statusLabel() {
                if (!this.result) return '—';
                return this.result.status === 'Layak'
                    ? @js(__('common.status.layak'))
                    : @js(__('common.status.tidak_layak'));
            },

            get dependantUnit() {
                return this.totalTanggungan === 1
                    ? @js(__('hadkifayah.needs.person_one'))
                    : @js(__('hadkifayah.needs.person_many'));
            },

            reset() {
                this.pendapatan_pemohon = 0;
                this.pendapatan_pasangan = 0;
                this.pendapatan_anak = 0;
                this.pendapatan_lain = 0;
                this.n_isteri_suami = 0;
                this.n_ibu_bapa = 0;
                this.n_dewasa_tidak_bekerja = 0;
                this.n_dewasa_bekerja_tanggung = 0;
                this.n_ipt = 0;
                this.n_remaja = 0;
                this.n_kanak_rendah = 0;
                this.n_kanak_tadika = 0;
                this.perumahan = 'sendiri';
                this.utiliti_input = 0;
                this.pengangkutan_input = 0;
                this.perubatan_input = 0;
                this.result = null;
            },

            printResult() {
                this.schedulePreview();
                setTimeout(() => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('hadkifayah.print') }}';
                    form.target = '_blank';
                    const fields = {
                        pendapatan_pemohon: this.pendapatan_pemohon,
                        pendapatan_pasangan: this.pendapatan_pasangan,
                        pendapatan_anak: this.pendapatan_anak,
                        pendapatan_lain: this.pendapatan_lain,
                        n_isteri_suami: this.n_isteri_suami,
                        n_ibu_bapa: this.n_ibu_bapa,
                        n_dewasa_tidak_bekerja: this.n_dewasa_tidak_bekerja,
                        n_dewasa_bekerja_tanggung: this.n_dewasa_bekerja_tanggung,
                        n_ipt: this.n_ipt,
                        n_remaja: this.n_remaja,
                        n_kanak_rendah: this.n_kanak_rendah,
                        n_kanak_tadika: this.n_kanak_tadika,
                        perumahan: this.perumahan,
                        utiliti_input: this.utiliti_input,
                        pengangkutan_input: this.pengangkutan_input,
                        perubatan_input: this.perubatan_input,
                    };
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                    form.appendChild(csrf);
                    for (const [k, v] of Object.entries(fields)) {
                        const i = document.createElement('input');
                        i.type = 'hidden';
                        i.name = k;
                        i.value = v;
                        form.appendChild(i);
                    }
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }, 400);
            },
        }"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
    >
        <div class="lg:col-span-2 space-y-6">
            <x-form-section :title="__('hadkifayah.income.title')" :description="__('hadkifayah.income.description')" icon="fa-wallet" accent="primary">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input :label="__('hadkifayah.income.applicant')" name="pendapatan_pemohon" type="number" step="0.01" min="0" x-model.number="pendapatan_pemohon" placeholder="0.00" icon="fa-user" />
                    <x-app-input :label="__('hadkifayah.income.spouse')" name="pendapatan_pasangan" type="number" step="0.01" min="0" x-model.number="pendapatan_pasangan" placeholder="0.00" icon="fa-user" />
                    <x-app-input :label="__('hadkifayah.income.child')" name="pendapatan_anak" type="number" step="0.01" min="0" x-model.number="pendapatan_anak" placeholder="0.00" icon="fa-children" />
                    <x-app-input :label="__('hadkifayah.income.other')" name="pendapatan_lain" type="number" step="0.01" min="0" x-model.number="pendapatan_lain" placeholder="0.00" icon="fa-coins" />
                </div>
                <div class="soft-panel p-4 flex items-center justify-between">
                    <span class="text-small text-secondary font-medium">{{ __('hadkifayah.income.total') }}</span>
                    <span class="text-metric text-primary tabular-nums" x-text="'RM ' + pendapatanTotal.toFixed(2)"></span>
                </div>
            </x-form-section>

            <x-form-section :title="__('hadkifayah.needs.title')" :description="__('hadkifayah.needs.description')" icon="fa-people-group" accent="success">
                <div class="space-y-5">
                    <div>
                        <p class="text-eyebrow text-tertiary mb-3">{{ __('hadkifayah.needs.head_category') }}</p>
                        <div class="flex items-center justify-between soft-panel p-3">
                            <span class="text-body text-text">{{ __('hadkifayah.needs.head') }}</span>
                            <span class="text-body text-text font-semibold">{{ __('hadkifayah.needs.head_count') }} <span class="text-secondary font-normal">(RM {{ number_format($rates['Ketua Keluarga'] ?? 0, 2) }})</span></span>
                        </div>
                    </div>

                    <div>
                        <p class="text-eyebrow text-tertiary mb-3">{{ __('hadkifayah.needs.adult_category') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-app-input :label="__('hadkifayah.needs.spouse')" name="n_isteri_suami" type="number" min="0" max="20" step="1" x-model.number="n_isteri_suami" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Dewasa']['Isteri / Suami'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.parents')" name="n_ibu_bapa" type="number" min="0" max="20" step="1" x-model.number="n_ibu_bapa" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Dewasa']['Ibu Bapa Tanggungan'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.adult_unemployed')" name="n_dewasa_tidak_bekerja" type="number" min="0" max="20" step="1" x-model.number="n_dewasa_tidak_bekerja" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Dewasa']['Dewasa Tidak Bekerja'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.adult_working_dependant')" name="n_dewasa_bekerja_tanggung" type="number" min="0" max="20" step="1" x-model.number="n_dewasa_bekerja_tanggung" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Dewasa']['Dewasa Bekerja Tetapi Masih Ditanggung'] ?? 0, 2)])" />
                        </div>
                    </div>

                    <div>
                        <p class="text-eyebrow text-tertiary mb-3">{{ __('hadkifayah.needs.education_category') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-app-input :label="__('hadkifayah.needs.higher_education')" name="n_ipt" type="number" min="0" max="20" step="1" x-model.number="n_ipt" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['IPT'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.teenager')" name="n_remaja" type="number" min="0" max="20" step="1" x-model.number="n_remaja" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Remaja'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.primary_school')" name="n_kanak_rendah" type="number" min="0" max="20" step="1" x-model.number="n_kanak_rendah" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Kanak-Kanak']['Sekolah Rendah'] ?? 0, 2)])" />
                            <x-app-input :label="__('hadkifayah.needs.kindergarten')" name="n_kanak_tadika" type="number" min="0" max="20" step="1" x-model.number="n_kanak_tadika" placeholder="0" :hint="__('hadkifayah.needs.rate_per_person', ['rate' => number_format($rates['Kanak-Kanak']['Tadika'] ?? 0, 2)])" />
                        </div>
                    </div>

                    <div class="soft-panel p-3 flex items-center gap-2 text-small">
                        <i class="fa-solid fa-circle-info text-primary text-[13px]"></i>
                        <span class="text-secondary">{{ __('hadkifayah.needs.total_dependants') }}</span>
                        <span class="text-text font-semibold" x-text="totalTanggungan + ' ' + dependantUnit"></span>
                    </div>
                </div>
            </x-form-section>

            <x-form-section :title="__('hadkifayah.housing.title')" :description="__('hadkifayah.housing.description')" icon="fa-house" accent="warning">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" x-model="perumahan" value="sendiri" class="peer sr-only">
                        <div class="p-4 rounded-[14px] border-2 transition-apple"
                             :class="perumahan === 'sendiri' ? 'border-primary bg-primary-soft shadow-sm' : 'border-divider bg-card hover:border-primary/50'">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-[12px] flex items-center justify-center" :class="perumahan === 'sendiri' ? 'bg-primary text-white' : 'bg-muted text-secondary'">
                                    <i class="fa-solid fa-house text-[18px]"></i>
                                </div>
                                <div>
                                    <p class="text-body text-text font-semibold">{{ __('hadkifayah.housing.own') }}</p>
                                    <p class="text-small text-secondary">{{ __('hadkifayah.housing.own_description') }}</p>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" x-model="perumahan" value="sewa" class="peer sr-only">
                        <div class="p-4 rounded-[14px] border-2 transition-apple"
                             :class="perumahan === 'sewa' ? 'border-primary bg-primary-soft shadow-sm' : 'border-divider bg-card hover:border-primary/50'">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-[12px] flex items-center justify-center" :class="perumahan === 'sewa' ? 'bg-primary text-white' : 'bg-muted text-secondary'">
                                    <i class="fa-solid fa-key text-[18px]"></i>
                                </div>
                                <div>
                                    <p class="text-body text-text font-semibold">{{ __('hadkifayah.housing.rent') }}</p>
                                    <p class="text-small text-secondary">{{ __('hadkifayah.housing.rent_description') }} <span class="text-text font-medium">+ RM {{ number_format($rates['Rumah Sewa'] ?? 0, 2) }}</span></p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </x-form-section>

            <x-form-section :title="__('hadkifayah.expenses.title')" :description="__('hadkifayah.expenses.description')" icon="fa-receipt" accent="info">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-app-input :label="__('hadkifayah.expenses.utilities')" name="utiliti_input" type="number" step="0.01" min="0" x-model.number="utiliti_input" placeholder="0.00" icon="fa-bolt" />
                    <x-app-input :label="__('hadkifayah.expenses.transport')" name="pengangkutan_input" type="number" step="0.01" min="0" x-model.number="pengangkutan_input" placeholder="0.00" icon="fa-bus" />
                    <x-app-input :label="__('hadkifayah.expenses.medical')" name="perubatan_input" type="number" step="0.01" min="0" x-model.number="perubatan_input" placeholder="0.00" icon="fa-briefcase-medical" />
                </div>
            </x-form-section>

            <div class="flex items-center gap-3 flex-wrap">
                <x-app-button variant="primary" size="lg" icon="fa-calculator" @click="runPreview()">{{ __('hadkifayah.buttons.recalculate') }}</x-app-button>
                <x-app-button variant="secondary" size="lg" icon="fa-rotate-left" @click="reset()">{{ __('hadkifayah.buttons.reset') }}</x-app-button>
                <x-app-button variant="outline" size="lg" icon="fa-print" @click="printResult()">{{ __('hadkifayah.buttons.print') }}</x-app-button>
            </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <x-result-card :label="__('hadkifayah.result.income_total')" :subtext="__('hadkifayah.result.income_subtext')" icon="fa-wallet" status="primary" accent="primary">
                <p class="text-metric text-text mt-1 tabular-nums" x-text="'RM ' + pendapatanTotal.toFixed(2)"></p>
                <p class="text-small text-secondary mt-2">{{ __('hadkifayah.income.auto_hint') }}</p>
            </x-result-card>

            <x-result-card :label="__('hadkifayah.result.threshold_total')" :subtext="__('hadkifayah.result.threshold_subtext')" icon="fa-calculator" status="primary" accent="success">
                <p class="text-metric text-text mt-1 tabular-nums" x-text="result ? 'RM ' + Number(result.had_kifayah_total).toFixed(2) : 'RM 0.00'"></p>
                <div class="mt-3 pt-3 border-t border-divider space-y-1.5 text-small" x-show="result">
                    <template x-for="row in (result ? result.breakdown : [])" :key="row.label">
                        <div class="flex justify-between gap-2">
                            <span class="text-secondary truncate" x-text="row.label"></span>
                            <span class="text-text font-medium tabular-nums" x-text="'RM ' + Number(row.subtotal).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
            </x-result-card>

            <x-result-card :label="__('hadkifayah.result.difference')" :subtext="__('hadkifayah.result.difference_subtext')" icon="fa-scale-balanced" x-bind:status="result && result.baki < 0 ? 'success' : 'danger'" x-bind:accent="result && result.baki < 0 ? 'success' : 'danger'">
                <p class="text-metric text-text mt-1 tabular-nums" x-text="result ? 'RM ' + Number(result.baki).toFixed(2) : 'RM 0.00'"></p>
            </x-result-card>

            <x-app-card class="p-6" :hover="false" elevated>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-eyebrow text-primary">{{ __('hadkifayah.assessment.eyebrow') }}</p>
                        <h2 class="text-h3 text-text mt-1">{{ __('hadkifayah.assessment.title') }}</h2>
                    </div>
                    <div class="w-11 h-11 rounded-[14px] bg-primary-soft text-primary flex items-center justify-center">
                        <i class="fa-solid fa-brain text-[16px]"></i>
                    </div>
                </div>

                <div x-show="loading" class="text-center py-4 text-small text-secondary flex items-center justify-center gap-2">
                    <span class="inline-flex items-center gap-1 align-middle">
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                    </span>
                    <span>{{ __('hadkifayah.assessment.calculating') }}</span>
                </div>

                <div x-show="!loading && !result" class="text-center py-4 text-small text-secondary">
                    {{ __('hadkifayah.assessment.empty') }}
                </div>

                <div x-show="!loading && result" class="space-y-4">
                    <div>
                        <p class="text-eyebrow text-tertiary mb-1.5">{{ __('hadkifayah.assessment.status') }}</p>
                        <x-status-badge status="primary" x-bind:status="statusVariant">
                            <span x-text="statusLabel"></span>
                        </x-status-badge>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-eyebrow text-tertiary">{{ __('hadkifayah.assessment.confidence') }}</p>
                            <p class="text-body text-text font-semibold tabular-nums" x-text="result ? result.confidence + '%' : '—'"></p>
                        </div>
                        <div class="h-2 bg-muted rounded-pill overflow-hidden">
                            <div class="h-full rounded-pill transition-all duration-700" :class="statusVariant === 'success' ? 'bg-success' : (statusVariant === 'danger' ? 'bg-danger' : 'bg-primary')" :style="'width: ' + (result ? result.confidence : 0) + '%'"></div>
                        </div>
                    </div>

                    <div>
                        <p class="text-eyebrow text-tertiary mb-1.5">{{ __('hadkifayah.assessment.recommendation') }}</p>
                        <p class="text-small text-text leading-relaxed" x-text="result ? result.recommendation : '—'"></p>
                    </div>

                    <div>
                        <p class="text-eyebrow text-tertiary mb-2">{{ __('hadkifayah.assessment.reasoning') }}</p>
                        <ul class="space-y-1.5 text-small">
                            <template x-for="(line, idx) in (result ? result.reasoning : [])" :key="idx">
                                <li class="text-text leading-relaxed" x-text="line"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </x-app-card>
        </div>
    </div>
@endsection
