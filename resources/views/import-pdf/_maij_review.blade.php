@php
    // MAIJ Form Understanding review panel — renders the structured ocr_result
    // produced by the document-intelligence pipeline.
    $ocr = $ocrResult ?? null;
    $fields = $ocr['fields'] ?? [];
    $checkboxes = $ocr['checkboxes'] ?? [];
    $applications = $ocr['applications'] ?? [];
    $dependents = $ocr['dependents'] ?? [];
    $income = $ocr['income'] ?? [];
    $expenses = $ocr['expenses'] ?? [];
    $declaration = $ocr['declaration'] ?? [];
    $verification = $ocr['verification'] ?? [];

    $tone = function ($conf) {
        $conf = (float) $conf;
        if ($conf >= 90) return ['success', 'Tinggi'];
        if ($conf >= 75) return ['warning', 'Sederhana'];
        if ($conf >= 60) return ['danger', 'Rendah'];
        return ['danger', 'Semakan'];
    };
    $review = function (array $field) {
        return ($field['needs_review'] ?? false) || (float) ($field['confidence'] ?? 0) < 60;
    };
    $fieldVal = function (array $field, string $key) {
        return $field['value'] ?? ($field[$key] ?? null);
    };
    $pageFields = function (int $page) use ($fields) {
        return array_filter($fields, fn ($f) => (int) ($f['page'] ?? 0) === $page && ($f['value'] ?? null) !== null);
    };
    $cbState = function ($state) {
        if ($state === true) return '<span class="text-success font-semibold">Tandai</span>';
        if ($state === false) return '<span class="text-tertiary">Kosong</span>';
        return '<span class="text-danger font-semibold">Tidak jelas</span>';
    };
    $sectionCards = [
        ['key' => 'sara_hidup', 'title' => 'Sara Hidup (5A)', 'icon' => 'fa-bowl-rice'],
        ['key' => 'medical', 'title' => 'Perubatan (5B)', 'icon' => 'fa-briefcase-medical'],
        ['key' => 'business', 'title' => 'Berdikari (5C)', 'icon' => 'fa-screwdriver-wrench'],
        ['key' => 'housing', 'title' => 'Perumahan (5D)', 'icon' => 'fa-house-medical'],
        ['key' => 'debt', 'title' => 'Menjelaskan Hutang (5E)', 'icon' => 'fa-coins'],
        ['key' => 'residence', 'title' => 'Tempat Tinggal', 'icon' => 'fa-house-chimney'],
        ['key' => 'assets', 'title' => 'Harta', 'icon' => 'fa-car-side'],
        ['key' => 'employment', 'title' => 'Pekerjaan', 'icon' => 'fa-briefcase'],
    ];
@endphp

@if($ocr)
    <x-app-card class="p-6 mb-6" :hover="false" accent="primary">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <x-section-header
                title="Maklumat Berjaya Dikesan"
                description="Pemahaman struktur borang — {{ $ocr['document']['document_type'] ?? 'Borang MAIJ' }} ({{ $ocr['document']['pages'] ?? '—' }} muka surat)"
                icon="fa-wand-magic-sparkles" />
            <div class="flex items-center gap-2">
                @if(($ocr['source'] ?? '') === 'vision')
                    <x-status-badge status="primary" size="sm" :dot="false">
                        <i class="fa-solid fa-eye text-[10px] mr-1"></i> Vision AI ({{ $ocr['provider'] ?? '—' }})
                    </x-status-badge>
                @else
                    <x-status-badge status="info" size="sm" :dot="false">
                        <i class="fa-solid fa-cpu text-[10px] mr-1"></i> Enjin Templat Setempat
                    </x-status-badge>
                @endif
                @php $sections = $ocr['document']['sections_detected'] ?? []; @endphp
                @if(count($sections))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($sections as $s)
                            <span class="text-[11px] font-semibold bg-primary-soft text-primary px-2 py-1 rounded-pill">
                                §{{ $s['number'] ?? '' }} · Muka {{ $s['page'] ?? '' }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Bantuan applications --}}
        @if(count($applications))
            <div class="mb-5">
                <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-heart text-primary text-[14px]"></i>
                    Bantuan Dipohon (Muka 1)
                </h4>
                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead><tr><th>Jenis Bantuan</th><th>Dipilih</th><th>Jumlah (RM)</th><th>Sebab / Ulasan</th><th>Sumber</th></tr></thead>
                        <tbody>
                            @foreach($applications as $app)
                                <tr>
                                    <td class="text-small text-text font-medium">{{ $app['type'] }}</td>
                                    <td class="text-small">
                                        @if($app['selected'])
                                            <x-status-badge status="success" size="sm">Dipilih</x-status-badge>
                                        @else
                                            <x-status-badge status="muted" size="sm">Tidak</x-status-badge>
                                        @endif
                                    </td>
                                    <td class="text-small text-text tabular-nums">{{ $app['amount_requested'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $app['reason'] ?? '—' }}</td>
                                    <td class="text-small text-tertiary">{{ $app['selection_source'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Checkbox groups --}}
        @if(count($checkboxes))
            <div class="mb-5">
                <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary text-[14px]"></i>
                    Pilihan Kotak Semak
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($checkboxes as $group => $options)
                        <div class="p-3 rounded-[12px] bg-muted/50 border border-divider/60">
                            <p class="text-eyebrow text-tertiary mb-1.5 uppercase">{{ str_replace('_', ' ', $group) }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($options as $opt => $r)
                                    <span class="inline-flex items-center gap-1.5 text-[12px] px-2 py-1 rounded-pill bg-card border border-divider">
                                        <span>{{ ucfirst($opt) }}:</span>
                                        {!! $cbState($r['value'] ?? null) !!}
                                        <span class="text-tertiary">({{ $r['confidence'] ?? 0 }}%)</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section cards (Sara Hidup, Perubatan, Berdikari, Perumahan, Hutang, Tempat Tinggal, Harta, Pekerjaan) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
            @foreach($sectionCards as $card)
                @php $group = $ocr[$card['key']] ?? []; @endphp
                @if(is_array($group) && count(array_filter($group, fn($f) => is_array($f) && ($f['value'] ?? null) !== null)))
                    <div class="p-4 rounded-[14px] bg-muted/40 border border-divider/60">
                        <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                            <i class="fa-solid {{ $card['icon'] }} text-primary text-[14px]"></i> {{ $card['title'] }}
                        </h4>
                        <dl class="space-y-1.5 text-small">
                            @foreach($group as $fkey => $field)
                                @if(is_array($field) && ($field['value'] ?? null) !== null)
                                    @php [$tone] = $tone($field['confidence'] ?? 0); @endphp
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-tertiary truncate">{{ ucwords(str_replace('_', ' ', $fkey)) }}</dt>
                                        <dd class="text-text font-medium text-right">
                                            {{ $field['value'] }}
                                            <span class="text-[10px] ml-1 font-semibold text-{{ $tone }}">({{ $field['confidence'] ?? 0 }}%)</span>
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Dependents table --}}
        @if(count($dependents))
            <div class="mb-5">
                <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-people-group text-primary text-[14px]"></i>
                    Tanggungan / Waris Yang Telah Bekerja (Muka 3)
                </h4>
                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr><th>#</th><th>Nama Penuh</th><th>No. K/P</th><th>Sekolah / Pekerjaan</th><th>Pendapatan</th><th>Hubungan</th><th>Tinggal</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($dependents as $d)
                                <tr class="{{ ($d['needs_review'] ?? false) && ($d['nama_penuh'] ?? null) ? 'bg-warning-soft/30' : '' }}">
                                    <td class="text-small text-tertiary">{{ $d['row'] }}</td>
                                    <td class="text-small text-text font-medium">{{ $d['nama_penuh'] ?? '—' }}</td>
                                    <td class="text-small text-text tabular-nums">{{ $d['no_ic'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $d['sekolah_pekerjaan'] ?? '—' }}</td>
                                    <td class="text-small text-text tabular-nums">{{ $d['pendapatan'] !== null ? 'RM'.number_format($d['pendapatan'], 2) : '—' }}</td>
                                    <td class="text-small text-text">{{ $d['hubungan'] ?? '—' }}</td>
                                    <td class="text-small text-text">{{ $d['tinggal_bersama'] ?? '—' }}</td>
                                    <td class="text-small">
                                        @if(($d['nama_penuh'] ?? null) === null)
                                            <span class="text-tertiary">—</span>
                                        @elseif($d['needs_review'] ?? false)
                                            <x-status-badge status="warning" size="sm">Semak</x-status-badge>
                                        @else
                                            <x-status-badge status="success" size="sm">OK</x-status-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Income & expenses --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
            <div>
                <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-primary text-[14px]"></i> Pendapatan Bulanan
                </h4>
                <table class="app-table">
                    <tbody>
                        @foreach($income as $k => $row)
                            <tr>
                                <td class="text-small text-text">{{ $row['source'] ?? $k }}</td>
                                <td class="text-small text-text tabular-nums text-right">{{ $row['value'] !== null ? 'RM'.number_format((float)$row['value'], 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                <h4 class="text-body font-semibold text-text mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary text-[14px]"></i> Perbelanjaan Bulanan
                </h4>
                <table class="app-table">
                    <tbody>
                        @foreach($expenses as $k => $row)
                            <tr>
                                <td class="text-small text-text">{{ $row['source'] ?? $k }}</td>
                                <td class="text-small text-text tabular-nums text-right">{{ $row['value'] !== null ? 'RM'.number_format((float)$row['value'], 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Audit Kewangan --}}
        @php
            $fa = $ocr['financial_audit'] ?? [];
            $faTone = match($fa['overall'] ?? '') { 'pass' => 'success', 'warn' => 'warning', 'fail' => 'danger', default => 'muted' };
        @endphp
        @if(!empty($fa))
            <div class="mb-5 p-4 rounded-[14px] border border-divider/60 @if(($fa['overall'] ?? '') === 'fail') bg-danger-soft/40 @elseif(($fa['overall'] ?? '') === 'warn') bg-warning-soft/40 @else bg-muted/40 @endif">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h4 class="text-body font-semibold text-text flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass-dollar text-primary text-[14px]"></i> Audit Kewangan
                    </h4>
                    <div class="flex items-center gap-2">
                        <span class="text-[12px] text-tertiary tabular-nums">Pendapatan RM{{ number_format((float)($fa['income_sum'] ?? 0), 2) }}</span>
                        <span class="text-[12px] text-tertiary tabular-nums">Perbelanjaan RM{{ number_format((float)($fa['expense_sum'] ?? 0), 2) }}</span>
                        <x-status-badge :status="$faTone" size="sm">
                            {{ match($fa['overall'] ?? '') { 'pass' => 'SELARAS', 'warn' => 'PERLU SEMAKAN', 'fail' => 'TIDAK SELARAS', default => 'TIADA DATA' } }}
                        </x-status-badge>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach(($fa['checks'] ?? []) as $check)
                        @php
                            $tone = match($check['status'] ?? '') { 'pass' => 'success', 'warn' => 'warning', 'fail' => 'danger', default => 'muted' };
                            $icon = match($check['status'] ?? '') { 'pass' => 'fa-circle-check', 'warn' => 'fa-triangle-exclamation', 'fail' => 'fa-circle-xmark', default => 'fa-circle-minus' };
                        @endphp
                        <div class="flex items-start gap-2 p-2.5 rounded-[10px] bg-card border border-divider/60">
                            <i class="fa-solid {{ $icon }} text-{{ $tone }} mt-0.5 text-[13px]"></i>
                            <p class="text-[12px] text-text">{{ $check['message'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Declaration & verification --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="p-4 rounded-[14px] bg-muted/40 border border-divider/60">
                <h4 class="text-body font-semibold text-text mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-signature text-primary text-[14px]"></i> Pengakuan Pemohon (Muka 4)
                </h4>
                <dl class="space-y-2 text-small">
                    <div class="flex justify-between"><dt class="text-tertiary">Nama</dt><dd class="text-text font-medium">{{ $declaration['pengakuan_nama']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-tertiary">Tarikh</dt><dd class="text-text">{{ $declaration['pengakuan_tarikh']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between items-center">
                        <dt class="text-tertiary">Tandatangan</dt>
                        <dd>
                            @php $sig = $declaration['signature'] ?? []; @endphp
                            @if(($sig['present'] ?? null) === true)
                                <x-status-badge status="success" size="sm">Ada ({{ $sig['confidence'] ?? 0 }}%)</x-status-badge>
                            @elseif(($sig['present'] ?? null) === false)
                                <x-status-badge status="muted" size="sm">Tidak dikesan</x-status-badge>
                            @else
                                <x-status-badge status="warning" size="sm">Tidak jelas</x-status-badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="p-4 rounded-[14px] bg-muted/40 border border-divider/60">
                <h4 class="text-body font-semibold text-text mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-primary text-[14px]"></i> Butiran Pengakuan / Pengesah
                </h4>
                <dl class="space-y-2 text-small">
                    <div class="flex justify-between"><dt class="text-tertiary">Nama</dt><dd class="text-text font-medium">{{ $verification['pengesah_nama']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-tertiary">Jawatan</dt><dd class="text-text">{{ $verification['pengesah_jawatan']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-tertiary">Ulasan</dt><dd class="text-text">{{ $verification['pengesah_ulasan']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-tertiary">Tarikh</dt><dd class="text-text">{{ $verification['pengesah_tarikh']['value'] ?? '—' }}</dd></div>
                    <div class="flex justify-between items-center">
                        <dt class="text-tertiary">Tandatangan / Cop</dt>
                        <dd>
                            @php $stamp = $verification['stamp'] ?? []; @endphp
                            @if(($stamp['present'] ?? null) === true)
                                <x-status-badge status="success" size="sm">Ada</x-status-badge>
                            @elseif(($stamp['present'] ?? null) === false)
                                <x-status-badge status="muted" size="sm">Tidak dikesan</x-status-badge>
                            @else
                                <x-status-badge status="warning" size="sm">Tidak jelas</x-status-badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </x-app-card>
@endif