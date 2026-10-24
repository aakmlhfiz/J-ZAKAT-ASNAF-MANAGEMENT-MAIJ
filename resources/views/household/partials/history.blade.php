@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assistanceForm', (householdId) => ({
            open: false,
            editing: false,
            assistanceId: null,
            form: {
                household_member_id: '',
                jenis_bantuan: '',
                amount: '',
                frequency: 'one_off',
                status: 'approved',
                approved_date: '',
                expiry_date: '',
                approval_officer: '',
                reference_number: '',
                description: '',
            },
            create() {
                this.editing = false;
                this.assistanceId = null;
                this.form = {
                    household_member_id: '',
                    jenis_bantuan: '',
                    amount: '',
                    frequency: 'one_off',
                    status: 'approved',
                    approved_date: '',
                    expiry_date: '',
                    approval_officer: '',
                    reference_number: '',
                    description: '',
                };
                this.open = true;
            },
            edit(item) {
                this.editing = true;
                this.assistanceId = item.id;
                this.form = {
                    household_member_id: item.household_member_id ?? '',
                    jenis_bantuan: item.jenis_bantuan ?? '',
                    amount: item.amount ?? '',
                    frequency: item.frequency ?? 'one_off',
                    status: item.status ?? 'approved',
                    approved_date: item.approved_date ?? '',
                    expiry_date: item.expiry_date ?? '',
                    approval_officer: item.approval_officer ?? '',
                    reference_number: item.reference_number ?? '',
                    description: item.description ?? '',
                };
                this.open = true;
            },
            close() {
                this.open = false;
            },
            formAction() {
                if (this.editing && this.assistanceId) {
                    return @json(url('/app/assistance')) + '/' + this.assistanceId;
                }
                return @json(url('/app/household')) + '/' + householdId + '/assistance';
            },
            formMethod() {
                return this.editing ? 'PUT' : 'POST';
            },
        }));
    });
</script>
@endpush

<div x-data="assistanceForm({{ $household->id }})">
<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
    <div>
        <h2 class="text-h3 text-text tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-tertiary text-[14px]"></i>
            Sejarah Bantuan Isi Rumah
        </h2>
        <p class="text-small text-secondary mt-1.5 max-w-prose">Rekod lengkap semua bantuan yang diterima</p>
    </div>
    <button @click="create()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-fast">
        <i class="fa-solid fa-circle-plus"></i>
        <span>Rekod Bantuan</span>
    </button>
</div>

@if($historyByYear->isEmpty())
    <x-empty-state icon="fa-clock-rotate-left" title="Tiada Sejarah Bantuan" description="Isi rumah ini belum mempunyai rekod bantuan." />
@else
    <div class="space-y-6">
        @foreach($historyByYear as $year => $items)
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-[6px] bg-primary text-white text-[13px] font-bold">{{ $year }}</span>
                    <span class="text-small text-secondary">{{ $items->count() }} rekod</span>
                </div>
                <div class="space-y-0">
                    @foreach($items as $item)
                        <div class="flex items-center gap-3 py-3 px-4 rounded-[8px] hover:bg-muted/50 transition-fast border-b border-divider last:border-0">
                            <div class="w-8 h-8 rounded-full {{ $item->status === 'approved' ? 'bg-success/10 text-success' : ($item->status === 'rejected' ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning') }} flex items-center justify-center shrink-0">
                                <i class="fa-solid {{ $item->status === 'approved' ? 'fa-circle-check' : ($item->status === 'rejected' ? 'fa-circle-xmark' : 'fa-clock') }} text-[12px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-small font-semibold text-text">{{ $item->member?->nama ?? 'N/A' }}</span>
                                    <span class="text-tertiary">·</span>
                                    <span class="text-small text-secondary">{{ $item->jenis_bantuan }}</span>
                                    <span class="text-small font-semibold {{ $item->status === 'approved' ? 'text-success' : ($item->status === 'rejected' ? 'text-danger' : 'text-warning') }}">
                                        RM {{ number_format($item->amount, 2) }}{{ $item->frequency === 'monthly' ? '/bln' : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-[11px] text-tertiary">{{ $item->approved_date?->format('d M Y') ?? 'N/A' }}</span>
                                    @if($item->reference_number)
                                        <span class="text-[11px] text-tertiary">#{{ $item->reference_number }}</span>
                                    @endif
                                    @if($item->description)
                                        <span class="text-[11px] text-tertiary truncate max-w-[200px]">{{ $item->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                @php
                                    $editData = $item->only(['id', 'household_member_id', 'jenis_bantuan', 'amount', 'frequency', 'status', 'approved_date', 'expiry_date', 'approval_officer', 'reference_number', 'description']);
                                    $editData['approved_date'] = $item->approved_date?->format('Y-m-d');
                                    $editData['expiry_date'] = $item->expiry_date?->format('Y-m-d');
                                @endphp
                                <button @click="edit(@json($editData))"
                                        class="w-8 h-8 rounded-[8px] flex items-center justify-center text-text-tertiary hover:text-primary hover:bg-primary/10 transition-fast" title="Kemaskini">
                                    <i class="fa-solid fa-pen text-[12px]"></i>
                                </button>
                                <form method="POST" action="{{ route('assistance.destroy', $item->id) }}"
                                      onsubmit="return confirm('Padam rekod bantuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-[8px] flex items-center justify-center text-text-tertiary hover:text-danger hover:bg-danger/10 transition-fast" title="Padam">
                                        <i class="fa-solid fa-trash text-[12px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif

<div x-show="open" x-cloak
     x-transition.opacity
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[90] flex items-center justify-center p-4"
     style="display: none;" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity class="absolute inset-0 -z-10 bg-text/40 backdrop-blur-sm" @click="close()"></div>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-card rounded-[20px] p-6 md:p-7 max-w-2xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-h3 text-text font-semibold" x-text="editing ? 'Kemaskini Bantuan' : 'Rekod Bantuan Baru'"></h2>
            <button @click="close()" class="w-8 h-8 rounded-[8px] flex items-center justify-center text-text-tertiary hover:bg-muted transition-fast">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form :action="formAction()" :method="formMethod()" class="space-y-4">
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Ahli Isi Rumah</label>
                    <select name="household_member_id" x-model="form.household_member_id" required
                            class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                        <option value="">Pilih ahli...</option>
                        @foreach($household->members as $member)
                            <option value="{{ $member->id }}">{{ $member->nama }} ({{ $member->hubungan }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Jenis Bantuan</label>
                    <select name="jenis_bantuan" x-model="form.jenis_bantuan" required
                            class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                        <option value="">Pilih jenis...</option>
                        @foreach(\App\Models\AssistanceHistory::jenisBantuanOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Amaun (RM)</label>
                    <input type="number" step="0.01" min="0" name="amount" x-model="form.amount" required
                           class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Kekerapan</label>
                    <select name="frequency" x-model="form.frequency"
                            class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                        <option value="one_off">Sekali (One-off)</option>
                        <option value="monthly">Bulanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Status</label>
                    <select name="status" x-model="form.status"
                            class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                        <option value="approved">Diluluskan</option>
                        <option value="pending">Dalam Proses</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Tarikh Lulus</label>
                    <input type="date" name="approved_date" x-model="form.approved_date"
                           class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Tarikh Tamat</label>
                    <input type="date" name="expiry_date" x-model="form.expiry_date"
                           class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Pegawai Kelulusan</label>
                    <input type="text" name="approval_officer" x-model="form.approval_officer" maxlength="255"
                           class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-text-secondary mb-1">No Rujukan</label>
                    <input type="text" name="reference_number" x-model="form.reference_number" maxlength="255"
                           class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-text-secondary mb-1">Keterangan</label>
                    <textarea name="description" x-model="form.description" rows="2" maxlength="1000"
                              class="w-full px-3 py-2 rounded-lg border border-border bg-bg text-sm resize-none"></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-fast">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    <span x-text="editing ? 'Simpan Perubahan' : 'Simpan Rekod'"></span>
                </button>
                <button type="button" @click="close()" class="px-5 py-2.5 rounded-xl border border-border text-sm font-semibold hover:bg-muted transition-fast">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
</div>