<x-section-header title="Matriks Bantuan Isi Rumah" icon="fa-table" description="Setiap bantuan yang diterima oleh setiap ahli keluarga" />

<x-app-card padding="p-0">
    <div class="overflow-x-auto">
        <table class="app-table is-compact">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-card z-10 text-[12px]">Ahli</th>
                    @foreach($benefitMatrix['columns'] as $col)
                        <th class="text-center text-[11px] font-semibold whitespace-nowrap px-3">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($benefitMatrix['rows'] as $row)
                    <tr class="hover:bg-muted/50 transition-fast">
                        <td class="sticky left-0 bg-card z-10 font-semibold text-text text-[13px] whitespace-nowrap">
                            <span>{{ $row['member'] }}</span>
                            <span class="text-[10px] text-tertiary font-normal ml-1.5">({{ $row['hubungan'] }})</span>
                        </td>
                        @foreach($benefitMatrix['columns'] as $col)
                            @php $data = $row[$col] ?? null; @endphp
                            <td class="text-center px-3">
                                @if($data)
                                    <div class="relative group inline-block">
                                        @if($data['status'] === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-pill bg-success-soft text-success">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span> YA
                                            </span>
                                        @elseif($data['status'] === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-pill bg-danger-soft text-danger">
                                                <span class="w-1.5 h-1.5 rounded-full bg-danger"></span> TOLAK
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-pill bg-warning-soft text-warning">
                                                <span class="w-1.5 h-1.5 rounded-full bg-warning"></span> {{ strtoupper($data['status']) }}
                                            </span>
                                        @endif
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-3 rounded-[10px] bg-gray-900 text-white text-[11px] shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-fast z-20 pointer-events-none">
                                            <p class="font-semibold text-[12px]">{{ $row['member'] }} · {{ $col }}</p>
                                            <div class="mt-1.5 space-y-0.5 text-white/80">
                                                <p>Status: <span class="text-white font-semibold">{{ ucfirst($data['status']) }}</span></p>
                                                <p>Jumlah: <span class="text-white font-semibold">RM {{ number_format($data['amount'], 2) }}</span></p>
                                                <p>Jenis: <span class="text-white font-semibold">{{ $data['frequency'] === 'monthly' ? 'Bulanan' : 'Sekali' }}</span></p>
                                                @if($data['approved_date'])<p>Tarikh: {{ $data['approved_date'] }}</p>@endif
                                                @if($data['expiry_date'])<p>Tamat: {{ $data['expiry_date'] }}</p>@endif
                                                @if($data['reference'])<p>Ruj: {{ $data['reference'] }}</p>@endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-tertiary text-[12px]">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-card>

<div class="mt-3 flex flex-wrap gap-4">
    <span class="inline-flex items-center gap-1.5 text-[11px] text-tertiary"><span class="w-2.5 h-2.5 rounded-full bg-success"></span> Diterima</span>
    <span class="inline-flex items-center gap-1.5 text-[11px] text-tertiary"><span class="w-2.5 h-2.5 rounded-full bg-warning"></span> Dalam Proses</span>
    <span class="inline-flex items-center gap-1.5 text-[11px] text-tertiary"><span class="w-2.5 h-2.5 rounded-full bg-danger"></span> Ditolak</span>
    <span class="inline-flex items-center gap-1.5 text-[11px] text-tertiary"><span class="text-tertiary">—</span> Tiada</span>
</div>
