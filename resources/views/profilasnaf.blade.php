@extends('layouts.app', [
    'title' => ($mode ?? null) === 'edit' ? 'Kemaskini Profil' : 'Profil Asnaf',
    'subtitle' => ($mode ?? null) === 'edit' ? 'Kemaskini maklumat permohonan' : 'Daftar permohonan bantuan baharu',
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
            get statusVariant() { return this.status === 'Layak' ? 'success' : 'danger'; }
        }"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
    >
        @csrf
        @if(($mode ?? null) === 'edit') @method('PUT') @endif

        <div class="lg:col-span-2 space-y-6">
            <x-form-section title="Maklumat Peribadi" description="Maklumat asas pemohon" icon="fa-user">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="Nama Penuh" name="nama" required placeholder="Seperti dalam kad pengenalan" :value="old('nama', $profil->nama ?? '')" icon="fa-user" />
                    <x-app-input label="No. Kad Pengenalan" name="ic" required placeholder="Contoh: 900101145678" :value="old('ic', $profil->ic ?? '')" :disabled="($mode ?? null) === 'edit'" icon="fa-id-card" />
                    <x-app-input label="No. Telefon" name="telefon" type="tel" icon="fa-phone" placeholder="0123456789" :value="old('telefon', $profil->telefon ?? '')" />
                    <x-app-select label="Hubungan" name="hubungan" placeholder="Pilih hubungan">
                        @php $selectedHubungan = old('hubungan', $profil->hubungan ?? ''); @endphp
                        <option value="Ketua Keluarga" @selected($selectedHubungan === 'Ketua Keluarga')>Ketua Keluarga</option>
                        <option value="Isteri" @selected($selectedHubungan === 'Isteri')>Isteri</option>
                        <option value="Anak" @selected($selectedHubungan === 'Anak')>Anak</option>
                        <option value="Lain-lain" @selected($selectedHubungan === 'Lain-lain')>Lain-lain</option>
                    </x-app-select>
                </div>
            </x-form-section>

            <x-form-section title="Maklumat Isi Rumah" description="Hubungan dengan ketua keluarga" icon="fa-people-roof">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="IC Ketua Isi Rumah" name="ic_ketua_rumah" placeholder="Kosongkan jika pemohon adalah ketua" :value="old('ic_ketua_rumah', $profil->ic_ketua_rumah ?? '')" icon="fa-id-card" />
                </div>
                <x-app-textarea label="Alamat" name="alamat" :rows="3" placeholder="Alamat penuh pemohon" :value="old('alamat', $profil->alamat ?? '')" icon="fa-location-dot" />
            </x-form-section>

            <x-form-section title="Maklumat Kewangan" description="Pendapatan dan perbelanjaan bulanan" icon="fa-wallet" accent="primary">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-app-input label="Pendapatan Bulanan (RM)" name="pendapatan" type="number" step="0.01" placeholder="0.00" x-model="pendapatan" :value="old('pendapatan', $profil->pendapatan ?? '')" icon="fa-money-bill" />
                    <x-app-input label="Bilangan Tanggungan" name="bilangan_tanggungan" type="number" placeholder="0" x-model="bilangan" :value="old('bilangan_tanggungan', $profil->bilangan_tanggungan ?? '')" icon="fa-people-group" />
                    <x-app-input label="Sewa Rumah (RM)" name="sewa" type="number" step="0.01" placeholder="0.00" x-model="sewa" :value="old('sewa', $profil->sewa ?? '')" icon="fa-house" />
                    <x-app-input label="Bil Utiliti (RM)" name="utiliti" type="number" step="0.01" placeholder="0.00" x-model="utiliti" :value="old('utiliti', $profil->utiliti ?? '')" icon="fa-bolt" />
                </div>
                <div class="soft-panel p-4 flex items-center gap-3 mt-2">
                    <i class="fa-solid fa-circle-info text-primary text-[14px]"></i>
                    <p class="text-small text-text flex-1">Penuh Had Kifayah akan dikira menggunakan enjin MAIDAM di latar belakang selepas permohonan dihantar.</p>
                </div>
            </x-form-section>

            <x-form-section title="Kandungan Isi Rumah (Had Kifayah)" description="Bilangan ahli mengikut kategori — diperlukan untuk enjin MAIDAM" icon="fa-people-group" accent="success">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <p class="col-span-full text-eyebrow text-tertiary mt-1 mb-1">Kategori Dewasa</p>
                    <x-app-input label="Isteri / Suami" name="n_isteri_suami" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_isteri_suami', $profil->n_isteri_suami ?? 0)" />
                    <x-app-input label="Ibu Bapa Tanggungan" name="n_ibu_bapa" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_ibu_bapa', $profil->n_ibu_bapa ?? 0)" />
                    <x-app-input label="Dewasa Tidak Bekerja" name="n_dewasa_tidak_bekerja" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_dewasa_tidak_bekerja', $profil->n_dewasa_tidak_bekerja ?? 0)" />
                    <x-app-input label="Dewasa Bekerja Ditanggung" name="n_dewasa_bekerja_tanggung" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_dewasa_bekerja_tanggung', $profil->n_dewasa_bekerja_tanggung ?? 0)" />
                    <p class="col-span-full text-eyebrow text-tertiary mt-3 mb-1">Kategori Pendidikan</p>
                    <x-app-input label="Dewasa Belajar IPT" name="n_ipt" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_ipt', $profil->n_ipt ?? 0)" />
                    <x-app-input label="Remaja Sekolah Menengah" name="n_remaja" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_remaja', $profil->n_remaja ?? 0)" />
                    <x-app-input label="Kanak-Kanak Sekolah Rendah" name="n_kanak_rendah" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_kanak_rendah', $profil->n_kanak_rendah ?? 0)" />
                    <x-app-input label="Kanak-Kanak Tadika" name="n_kanak_tadika" type="number" min="0" max="20" step="1" placeholder="0" :value="old('n_kanak_tadika', $profil->n_kanak_tadika ?? 0)" />
                    <p class="col-span-full text-eyebrow text-tertiary mt-3 mb-1">Perumahan &amp; Perbelanjaan</p>
                    <x-app-select label="Status Perumahan" name="perumahan" x-model="perumahan">
                        <option value="sendiri" @selected(old('perumahan', $profil->perumahan ?? 'sendiri') === 'sendiri')>Rumah Sendiri</option>
                        <option value="sewa" @selected(old('perumahan', $profil->perumahan ?? 'sendiri') === 'sewa')>Rumah Sewa</option>
                    </x-app-select>
                    <x-app-input label="Pengangkutan (RM)" name="pengangkutan_input" type="number" step="0.01" placeholder="0.00" :value="old('pengangkutan_input', $profil->pengangkutan_input ?? 0)" icon="fa-bus" />
                    <x-app-input label="Perubatan (RM)" name="perubatan_input" type="number" step="0.01" placeholder="0.00" :value="old('perubatan_input', $profil->perubatan_input ?? 0)" icon="fa-briefcase-medical" />
                </div>
            </x-form-section>

            <div class="flex items-center gap-3 sticky bottom-0 -mx-6 md:-mx-10 -mb-6 md:-mb-8 px-6 md:px-10 py-4 bg-bg/80 backdrop-blur border-t border-divider">
                <x-app-button type="submit" variant="primary" size="lg" icon="fa-check">{{ ($mode ?? null) === 'edit' ? 'Kemaskini Profil' : 'Simpan Permohonan' }}</x-app-button>
                <x-app-button variant="ghost" size="lg" href="{{ ($mode ?? null) === 'edit' ? route('viewprofile', $profil->ic) : route('viewprofile.index') }}">Batal</x-app-button>
            </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
            <x-result-card label="Penilaian Awal" subtext="Had Kifayah yang dikira" icon="fa-calculator" status="primary" accent="primary">
                <div class="space-y-3 text-small">
                    <div class="flex items-center justify-between"><span class="text-secondary">Asas</span><span class="font-medium text-text tabular-nums">RM 1,200.00</span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">Tanggungan</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + (Number(bilangan || 0) * 350).toFixed(2)"></span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">Sewa</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + Number(sewa || 0).toFixed(2)"></span></div>
                    <div class="flex items-center justify-between"><span class="text-secondary">Utiliti</span><span class="font-medium text-text tabular-nums" x-text="'RM ' + Number(utiliti || 0).toFixed(2)"></span></div>
                    <div class="pt-3 border-t border-divider flex items-center justify-between">
                        <span class="text-text font-semibold">Jumlah</span>
                        <span class="text-metric-sm text-text tabular-nums" x-text="'RM ' + hadKifayah.toFixed(2)"></span>
                    </div>
                </div>
            </x-result-card>

            <x-result-card label="Status Kelayakan" subtext="Berdasarkan data terkini" icon="fa-shield-halved" x-bind:status="statusVariant" accent="success">
                <p class="text-body text-text" x-text="status === 'Layak' ? 'Pemohon berkemungkinan layak menerima bantuan.' : 'Pendapatan melebihi Had Kifayah. Pemohon tidak layak pada masa ini.'"></p>
                <div class="mt-3 flex items-center gap-2">
                    <x-status-badge x-bind:status="statusVariant"><span x-text="status"></span></x-status-badge>
                </div>
            </x-result-card>
        </div>
    </form>
@endsection
