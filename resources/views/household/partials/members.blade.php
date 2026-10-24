<x-section-header title="Ahli Isi Rumah" icon="fa-users" description="{{ $household->total_members }} orang ahli keluarga" />

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @php
        $relationIcons = [
            'Ketua Isi Rumah' => ['icon' => 'fa-crown', 'bg' => 'bg-primary/15', 'text' => 'text-primary'],
            'Isteri' => ['icon' => 'fa-heart', 'bg' => 'bg-pink-500/15', 'text' => 'text-pink-500'],
            'Suami' => ['icon' => 'fa-heart', 'bg' => 'bg-primary-soft', 'text' => 'text-primary'],
            'Anak' => ['icon' => 'fa-child', 'bg' => 'bg-success/15', 'text' => 'text-success'],
            'Ibu' => ['icon' => 'fa-person-cane', 'bg' => 'bg-warning/15', 'text' => 'text-warning'],
            'Bapa' => ['icon' => 'fa-person-cane', 'bg' => 'bg-warning/15', 'text' => 'text-warning'],
        ];
    @endphp

    @foreach($household->members as $member)
        @php
            $ri = $relationIcons[$member->hubungan] ?? ['icon' => 'fa-user', 'bg' => 'bg-muted', 'text' => 'text-secondary'];
        @endphp
        <div class="surface surface-hover p-5 transition-apple">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-full {{ $ri['bg'] }} {{ $ri['text'] }} flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $ri['icon'] }} text-[16px]"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-body font-semibold text-text truncate">{{ $member->nama }}</p>
                    <p class="text-small text-primary font-semibold">{{ $member->ic }}</p>
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-pill bg-primary-soft text-primary">
                            {{ $member->hubungan }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-pill bg-muted text-secondary">
                            {{ $member->umur }} thn
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-divider">
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Pekerjaan</p>
                    <p class="text-small text-text font-medium mt-0.5 truncate">{{ $member->pekerjaan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Pendidikan</p>
                    <p class="text-small text-text font-medium mt-0.5 truncate">{{ $member->pendidikan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Pendapatan</p>
                    <p class="text-small text-text font-semibold mt-0.5">RM {{ number_format($member->pendapatan_bulanan, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Kesihatan</p>
                    <p class="text-small text-text font-medium mt-0.5 truncate">{{ $member->status_kesihatan ?? 'Sihat' }}</p>
                </div>
            </div>

            @if($member->assistance_status)
                <div class="mt-3 pt-3 border-t border-divider">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-pill bg-info-soft text-info">
                        <span class="w-1.5 h-1.5 rounded-full bg-info"></span> {{ $member->assistance_status }}
                    </span>
                </div>
            @endif
        </div>
    @endforeach
</div>
