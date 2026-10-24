@extends('portal.layouts.app')
@section('title', 'Kalkulator Had Kifayah — Portal Pemohon')
@section('page-title', 'Kalkulator Had Kifayah')

@section('content')
@php $user = auth('portal')->user(); @endphp

<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-title text-text">Kalkulator Had Kifayah</h1>
        <p class="text-body text-secondary mt-1">Kira anggaran had kifayah berdasarkan maklumat isi rumah anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-3">
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-tertiary text-[14px]"></i>
                    Maklumat Isi Rumah
                </h3>
                <form method="POST" action="{{ route('user.hadkifayah.calculate') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Pendapatan Pemohon (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="pendapatan_pemohon" required min="0" step="0.01"
                                value="{{ old('pendapatan_pemohon', $validated['pendapatan_pemohon'] ?? '') }}"
                                class="input-base w-full @error('pendapatan_pemohon') input-error @enderror">
                            @error('pendapatan_pemohon') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Pendapatan Pasangan (RM)</label>
                            <input type="number" name="pendapatan_pasangan" min="0" step="0.01"
                                value="{{ old('pendapatan_pasangan', $validated['pendapatan_pasangan'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                    </div>

                    <p class="text-small text-secondary mt-5 mb-3">Bilangan Tanggungan Isi Rumah</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                        $tanggunganFields = [
                            'n_isteri_suami' => 'Isteri / Suami',
                            'n_ibu_bapa' => 'Ibu Bapa',
                            'n_dewasa_tidak_bekerja' => 'Dewasa Tdk Bekerja',
                            'n_dewasa_bekerja_tanggung' => 'Dewasa Bekerja Ditanggung',
                            'n_ipt' => 'IPT',
                            'n_remaja' => 'Remaja',
                            'n_kanak_rendah' => 'Kanak Rendah',
                            'n_kanak_tadika' => 'Kanak Tadika',
                        ];
                        @endphp
                        @foreach($tanggunganFields as $field => $label)
                        <div>
                            <label class="block text-caption text-secondary mb-1">{{ $label }}</label>
                            <input type="number" name="{{ $field }}" min="0" max="10"
                                value="{{ old($field, $validated[$field] ?? 0) }}"
                                class="input-base w-full text-center">
                        </div>
                        @endforeach
                    </div>

                    <p class="text-small text-secondary mt-5 mb-3">Perbelanjaan Bulanan</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Jenis Perumahan <span class="text-danger">*</span></label>
                            <select name="perumahan" required class="input-base w-full">
                                <option value="sendiri" {{ old('perumahan', $validated['perumahan'] ?? 'sendiri') === 'sendiri' ? 'selected' : '' }}>Rumah Sendiri</option>
                                <option value="sewa" {{ old('perumahan', $validated['perumahan'] ?? '') === 'sewa' ? 'selected' : '' }}>Sewa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Utiliti (RM)</label>
                            <input type="number" name="utiliti_input" min="0" step="0.01"
                                value="{{ old('utiliti_input', $validated['utiliti_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Pengangkutan (RM)</label>
                            <input type="number" name="pengangkutan_input" min="0" step="0.01"
                                value="{{ old('pengangkutan_input', $validated['pengangkutan_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">Perubatan (RM)</label>
                            <input type="number" name="perubatan_input" min="0" step="0.01"
                                value="{{ old('perubatan_input', $validated['perubatan_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary btn-md">
                            <i class="fa-solid fa-calculator text-[13px]"></i>
                            <span>Kira Had Kifayah</span>
                        </button>
                        <a href="{{ route('user.hadkifayah') }}" class="btn btn-ghost btn-md">Reset</a>
                    </div>
                </form>
            </x-app-card>
        </div>

        <div class="lg:col-span-2">
            @if(isset($result))
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-tertiary text-[14px]"></i>
                    Keputusan
                </h3>
                <div class="space-y-4">
                    <div class="text-center py-3 rounded-xl {{ $result['status'] === 'Layak' ? 'bg-success-soft' : 'bg-danger-soft' }}">
                        <p class="text-caption {{ $result['status'] === 'Layak' ? 'text-success' : 'text-danger' }} mb-1">Status Kelayakan</p>
                        <p class="text-title font-bold {{ $result['status'] === 'Layak' ? 'text-success' : 'text-danger' }}">{{ $result['status'] }}</p>
                        @if($result['confidence'])
                        <p class="text-caption text-secondary mt-1">Keyakinan: {{ $result['confidence'] }}%</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-small text-secondary">Jumlah Pendapatan</span>
                            <span class="text-body text-text font-semibold">RM {{ number_format($result['pendapatan_total'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-small text-secondary">Had Kifayah</span>
                            <span class="text-body text-text font-semibold">RM {{ number_format($result['had_kifayah_total'], 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-divider flex justify-between">
                            <span class="text-body text-text font-semibold">Baki</span>
                            <span class="text-body font-bold {{ $result['baki'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $result['baki'] >= 0 ? '+' : '' }}RM {{ number_format($result['baki'], 2) }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($result['reasoning']))
                    <div class="pt-3 border-t border-divider">
                        <p class="text-small font-medium text-text mb-2">Justifikasi</p>
                        <ul class="space-y-1">
                            @foreach($result['reasoning'] as $reason)
                            <li class="text-small text-secondary flex items-start gap-2">
                                <i class="fa-solid fa-circle text-[4px] text-tertiary mt-[7px] shrink-0"></i>
                                <span>{{ $reason }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </x-app-card>
            @else
            <x-app-card class="p-5">
                <div class="py-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-calculator text-[22px] opacity-25"></i>
                    </div>
                    <p class="text-body text-secondary">Isi maklumat di sebelah dan klik <strong>Kira Had Kifayah</strong>.</p>
                </div>
            </x-app-card>
            @endif
        </div>
    </div>
</div>
@endsection
