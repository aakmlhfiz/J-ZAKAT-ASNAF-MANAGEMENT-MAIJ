@extends('portal.layouts.app')
@section('title', 'Permohonan Saya — Portal Pemohon')
@section('page-title', 'Permohonan Saya')

@section('content')
@php
    $user = auth('portal')->user();
    $hasDraft = ($stats['draft'] ?? 0) > 0;

    $statusMeta = [
        'draft'        => ['label' => 'Draf',           'color' => 'warning', 'pct' => 25,  'bar' => 'bg-warning'],
        'incomplete'   => ['label' => 'Tidak Lengkap',  'color' => 'warning', 'pct' => 50,  'bar' => 'bg-warning'],
        'submitted'    => ['label' => 'Dihantar',       'color' => 'primary', 'pct' => 50,  'bar' => 'bg-primary'],
        'under_review' => ['label' => 'Dalam Semakan',  'color' => 'info',    'pct' => 75,  'bar' => 'bg-info'],
        'approved'     => ['label' => 'Diluluskan',     'color' => 'success', 'pct' => 100, 'bar' => 'bg-success'],
        'rejected'     => ['label' => 'Ditolak',        'color' => 'danger',  'pct' => 100, 'bar' => 'bg-danger'],
    ];

    $items = $applications->getCollection()->map(function ($app) use ($statusMeta) {
        $meta = $statusMeta[$app->status] ?? ['label' => ucfirst($app->status), 'color' => 'secondary', 'pct' => 0, 'bar' => 'bg-secondary'];
        $canDownload = in_array($app->status, ['approved', 'rejected']) && \App\Models\ProfilAsnaf::where('ic', $app->ic_no)->value('letter_path');
        return [
            'id'             => $app->id,
            'application_no' => $app->application_no,
            'status'         => $app->status,
            'status_label'   => $meta['label'],
            'status_color'   => $meta['color'],
            'pct'            => $meta['pct'],
            'bar'            => $meta['bar'],
            'household_count' => $app->household_count,
            'occupation'     => $app->occupation,
            'full_name'      => $app->full_name,
            'created_at'     => $app->created_at->format('d M Y'),
            'total_income'   => (float) $app->total_household_income,
            'submitted_human' => $app->submitted_at?->diffForHumans(),
            'show_url'       => route('user.applications.show', $app->id),
            'can_download'   => $canDownload,
            'letter_url'     => route('user.applications.letter', $app->id),
        ];
    })->values()->all();
@endphp

<div
    x-data='applicationsPage(@json($items))'
    x-init="init()"
>

    {{-- Page Header --}}
    <x-page-header
        title="Permohonan Saya"
        subtitle="Senarai permohonan bantuan asnaf anda. Semak status, hantar draf, atau mohon baharu."
    >
        <x-slot:actions>
            <x-app-button
                variant="outline"
                size="md"
                icon="fa-file-contract"
                :href="route('user.applications.scan.index')"
            >
                Imbas Borang MAIJ
            </x-app-button>
            <x-app-button
                variant="primary"
                size="md"
                icon="fa-plus"
                :href="route('user.applications.create')"
            >
                Mohon Bantuan
            </x-app-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Stat Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger">
        <x-stat-card
            label="Jumlah"
            :value="$stats['total']"
            icon="fa-file-lines"
            iconBg="bg-primary-soft"
            iconColor="text-primary"
            accent="primary"
            href="#list"
            @click.prevent="setStatus('all')"
        />
        <x-stat-card
            label="Draf"
            :value="$stats['draft']"
            icon="fa-pen-to-square"
            iconBg="bg-warning-soft"
            iconColor="text-warning"
            accent="warning"
            href="#list"
            @click.prevent="setStatus('draft')"
        />
        <x-stat-card
            label="Dihantar / Semakan"
            :value="$stats['submitted']"
            icon="fa-paper-plane"
            iconBg="bg-info-soft"
            iconColor="text-info"
            accent="info"
            href="#list"
            @click.prevent="setStatus('submitted')"
        />
        <x-stat-card
            label="Diluluskan"
            :value="$stats['approved']"
            icon="fa-circle-check"
            iconBg="bg-success-soft"
            iconColor="text-success"
            accent="success"
            href="#list"
            @click.prevent="setStatus('approved')"
        />
    </div>

    {{-- Active Draft Banner --}}
    @if($hasDraft && $latestDraft)
        <x-app-card class="p-4 mb-5 border border-warning/30" style="background: color-mix(in srgb, theme(colors.warning.soft) 50%, transparent);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-warning-soft text-warning flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-pen-to-square text-[16px]"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-body text-text font-semibold">
                        Anda ada {{ $stats['draft'] }} draf yang belum dihantar
                    </p>
                    <p class="text-small text-secondary mt-0.5">
                        Draf terkini: <span class="font-medium text-text">{{ $latestDraft->application_no }}</span>
                        · dicipta {{ $latestDraft->created_at->diffForHumans() }}
                    </p>
                </div>
                <x-app-button
                    variant="primary"
                    size="sm"
                    icon="fa-arrow-right"
                    :href="route('user.applications.show', $latestDraft->id)"
                >
                    Sambung draf
                </x-app-button>
            </div>
        </x-app-card>
    @endif

    {{-- Filter Bar --}}
    <x-app-card class="p-4 mb-5">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="relative flex-1 min-w-0">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[13px] pointer-events-none"></i>
                <input
                    type="search"
                    x-model="q"
                    @input="visible = matches()"
                    placeholder="Cari no. permohonan, nama, atau pekerjaan..."
                    class="input-base w-full pl-10"
                >
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="relative">
                    <select x-model="status" @change="visible = matches()" class="input-base appearance-none pr-9 pl-9 min-w-[160px]">
                        <option value="all">Semua Status</option>
                        <option value="draft">Draf</option>
                        <option value="submitted">Dihantar</option>
                        <option value="under_review">Dalam Semakan</option>
                        <option value="approved">Diluluskan</option>
                        <option value="rejected">Ditolak</option>
                        <option value="incomplete">Tidak Lengkap</option>
                    </select>
                    <i class="fa-solid fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[12px] pointer-events-none"></i>
                    <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-tertiary text-[11px] pointer-events-none"></i>
                </div>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between gap-2 text-small text-secondary">
            <p>
                <span x-text="visible.length"></span>
                dipaparkan
                <span class="text-tertiary">/ {{ $applications->total() }} jumlah</span>
            </p>
            <button
                type="button"
                x-show="status !== 'all' || q.trim()"
                @click="resetFilters()"
                class="inline-flex items-center gap-1.5 text-primary hover:underline font-medium"
            >
                <i class="fa-solid fa-xmark text-[10px]"></i>
                Reset penapis
            </button>
        </div>
    </x-app-card>

    {{-- Application List --}}
    <div id="list" x-ref="list" class="space-y-3">
        <template x-for="item in visible" :key="item.id">
            <a :href="item.show_url" class="block group">
                <x-app-card class="p-5" hover>
                    <div class="flex items-start gap-4">
                        <div :class="`w-12 h-12 rounded-[14px] bg-${item.status_color}-soft text-${item.status_color} flex items-center justify-center shrink-0`">
                            <i class="fa-solid fa-file-lines text-[18px]"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div class="min-w-0">
                                    <p class="text-body text-text font-semibold truncate" x-text="item.application_no"></p>
                                    <p class="text-small text-secondary mt-0.5">
                                        <span x-text="item.created_at"></span>
                                        <template x-if="item.household_count">
                                            <span> · <span x-text="item.household_count"></span> isi rumah</span>
                                        </template>
                                        <template x-if="item.occupation">
                                            <span> · <span x-text="item.occupation"></span></span>
                                        </template>
                                    </p>
                                </div>
                                <span :class="`inline-flex items-center gap-1.5 font-semibold px-2.5 py-1 rounded-pill text-[12px] bg-${item.status_color}-soft text-${item.status_color}`">
                                    <span :class="`w-1.5 h-1.5 rounded-full bg-${item.status_color}`"></span>
                                    <span x-text="item.status_label"></span>
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-3 text-small text-secondary">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-coins text-tertiary text-[11px]"></i>
                                    RM <span x-text="item.total_income.toLocaleString('en-MY', {maximumFractionDigits: 0})"></span> / bulan
                                </span>
                                <template x-if="item.submitted_human">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="text-tertiary">·</span>
                                        <i class="fa-solid fa-paper-plane text-tertiary text-[11px]"></i>
                                        Dihantar <span x-text="item.submitted_human"></span>
                                    </span>
                                </template>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-[11px] text-secondary mb-1">
                                    <span>Progress</span>
                                    <span class="font-medium" x-text="`${item.pct}%`"></span>
                                </div>
                                <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                                    <div :class="`h-full ${item.bar} rounded-full transition-all duration-500`" :style="`width: ${item.pct}%`"></div>
                                </div>
                            </div>
                        </div>
                        <div class="hidden sm:flex items-center self-center gap-2 shrink-0">
                            <template x-if="item.can_download">
                                <a :href="item.letter_url" @click.stop class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-primary/10 text-primary text-[11px] font-semibold hover:bg-primary/20 transition-colors">
                                    <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                    <span class="hidden lg:inline">Surat</span>
                                </a>
                            </template>
                            <span class="text-tertiary group-hover:text-primary group-hover:translate-x-1 transition-all">
                                <i class="fa-solid fa-chevron-right text-[14px]"></i>
                            </span>
                        </div>
                    </div>
                </x-app-card>
            </a>
        </template>

        {{-- Filtered-empty state --}}
        <template x-if="visible.length === 0">
            <x-app-card>
                <x-empty-state
                    icon="fa-magnifying-glass"
                    title="Tiada padanan"
                    description="Tiada permohonan yang sepadan dengan penapis semasa. Cuba ubah penapis atau reset untuk melihat semua permohonan."
                />
            </x-app-card>
        </template>

        {{-- No-data state (server-side empty) --}}
        @if($applications->count() === 0)
            <x-app-card>
                <x-empty-state
                    icon="fa-folder-open"
                    title="Tiada permohonan"
                    description="Anda belum membuat sebarang permohonan bantuan asnaf. Mulakan permohonan pertama anda hari ini."
                >
                    <x-slot:action>
                        <x-app-button
                            variant="primary"
                            size="md"
                            icon="fa-plus"
                            :href="route('user.applications.create')"
                        >
                            Mohon Bantuan Sekarang
                        </x-app-button>
                    </x-slot:action>
                </x-empty-state>
            </x-app-card>
        @endif
    </div>

    {{-- Pagination --}}
    @if($applications->count() > 0)
        <div class="mt-6">
            {{ $applications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    function applicationsPage(items) {
        return {
            items: items,
            status: 'all',
            q: '',
            visible: items,
            init() {
                this.visible = this.matches();
            },
            matches() {
                const status = this.status;
                const q = this.q.trim().toLowerCase();
                return this.items.filter((it) => {
                    if (status !== 'all' && it.status !== status) return false;
                    if (q) {
                        const hay = (it.application_no + ' ' + (it.full_name || '') + ' ' + (it.occupation || '')).toLowerCase();
                        if (!hay.includes(q)) return false;
                    }
                    return true;
                });
            },
            setStatus(s) {
                this.status = s;
                this.visible = this.matches();
                const el = document.getElementById('list');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            },
            resetFilters() {
                this.status = 'all';
                this.q = '';
                this.visible = this.matches();
            },
        };
    }
</script>
@endpush
@endsection

