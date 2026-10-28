@extends('portal.layouts.app')
@section('title', __('portal_household.hadkifayah.title').' — '.__('navigation.brand.portal'))
@section('page-title', __('portal_household.hadkifayah.title'))

@section('content')
@php $user = auth('portal')->user(); @endphp

<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-title text-text">{{ __('portal_household.hadkifayah.title') }}</h1>
        <p class="text-body text-secondary mt-1">{{ __('portal_household.hadkifayah.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-3">
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-tertiary text-[14px]"></i>
                    {{ __('portal_household.hadkifayah.household_info') }}
                </h3>
                <form method="POST" action="{{ route('user.hadkifayah.calculate') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.applicant_income') }} <span class="text-danger">*</span></label>
                            <input type="number" name="pendapatan_pemohon" required min="0" step="0.01"
                                value="{{ old('pendapatan_pemohon', $validated['pendapatan_pemohon'] ?? '') }}"
                                class="input-base w-full @error('pendapatan_pemohon') input-error @enderror">
                            @error('pendapatan_pemohon') <p class="text-small text-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.spouse_income') }}</label>
                            <input type="number" name="pendapatan_pasangan" min="0" step="0.01"
                                value="{{ old('pendapatan_pasangan', $validated['pendapatan_pasangan'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                    </div>

                    <p class="text-small text-secondary mt-5 mb-3">{{ __('portal_household.hadkifayah.fields.dependants') }}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                        $tanggunganFields = [
                            'n_isteri_suami' => __('portal_household.hadkifayah.fields.n_isteri_suami'),
                            'n_ibu_bapa' => __('portal_household.hadkifayah.fields.n_ibu_bapa'),
                            'n_dewasa_tidak_bekerja' => __('portal_household.hadkifayah.fields.n_dewasa_tidak_bekerja'),
                            'n_dewasa_bekerja_tanggung' => __('portal_household.hadkifayah.fields.n_dewasa_bekerja_tanggung'),
                            'n_ipt' => __('portal_household.hadkifayah.fields.n_ipt'),
                            'n_remaja' => __('portal_household.hadkifayah.fields.n_remaja'),
                            'n_kanak_rendah' => __('portal_household.hadkifayah.fields.n_kanak_rendah'),
                            'n_kanak_tadika' => __('portal_household.hadkifayah.fields.n_kanak_tadika'),
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

                    <p class="text-small text-secondary mt-5 mb-3">{{ __('portal_household.hadkifayah.fields.monthly_expenses') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.housing_type') }} <span class="text-danger">*</span></label>
                            <select name="perumahan" required class="input-base w-full">
                                <option value="sendiri" {{ old('perumahan', $validated['perumahan'] ?? 'sendiri') === 'sendiri' ? 'selected' : '' }}>{{ __('portal_household.hadkifayah.fields.own_home') }}</option>
                                <option value="sewa" {{ old('perumahan', $validated['perumahan'] ?? '') === 'sewa' ? 'selected' : '' }}>{{ __('portal_household.hadkifayah.fields.rented_home') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.utilities') }}</label>
                            <input type="number" name="utiliti_input" min="0" step="0.01"
                                value="{{ old('utiliti_input', $validated['utiliti_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.transport') }}</label>
                            <input type="number" name="pengangkutan_input" min="0" step="0.01"
                                value="{{ old('pengangkutan_input', $validated['pengangkutan_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                        <div>
                            <label class="block text-small font-medium text-text mb-1.5">{{ __('portal_household.hadkifayah.fields.medical') }}</label>
                            <input type="number" name="perubatan_input" min="0" step="0.01"
                                value="{{ old('perubatan_input', $validated['perubatan_input'] ?? '') }}"
                                class="input-base w-full">
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary btn-md">
                            <i class="fa-solid fa-calculator text-[13px]"></i>
                            <span>{{ __('portal_household.hadkifayah.calculate') }}</span>
                        </button>
                        <a href="{{ route('user.hadkifayah') }}" class="btn btn-ghost btn-md">{{ __('portal_household.hadkifayah.reset') }}</a>
                    </div>
                </form>
            </x-app-card>
        </div>

        <div class="lg:col-span-2">
            @if(isset($result))
            <x-app-card class="p-5">
                <h3 class="text-h3 text-text mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-tertiary text-[14px]"></i>
                    {{ __('portal_household.hadkifayah.result.title') }}
                </h3>
                <div class="space-y-4">
                    <div class="text-center py-3 rounded-xl {{ $result['status'] === 'Layak' ? 'bg-success-soft' : 'bg-danger-soft' }}">
                        <p class="text-caption {{ $result['status'] === 'Layak' ? 'text-success' : 'text-danger' }} mb-1">{{ __('portal_household.hadkifayah.result.status_label') }}</p>
                        <p class="text-title font-bold {{ $result['status'] === 'Layak' ? 'text-success' : 'text-danger' }}">{{ match($result['status']) { 'Layak' => __('common.status.layak'), 'Tidak Layak' => __('common.status.tidak_layak'), default => $result['status'] } }}</p>
                        @if($result['confidence'])
                        <p class="text-caption text-secondary mt-1">{{ __('portal_household.hadkifayah.result.confidence', ['value' => $result['confidence']]) }}</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-small text-secondary">{{ __('portal_household.hadkifayah.result.income_total') }}</span>
                            <span class="text-body text-text font-semibold">RM {{ number_format($result['pendapatan_total'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-small text-secondary">{{ __('portal_household.hadkifayah.result.had_kifayah') }}</span>
                            <span class="text-body text-text font-semibold">RM {{ number_format($result['had_kifayah_total'], 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-divider flex justify-between">
                            <span class="text-body text-text font-semibold">{{ __('portal_household.hadkifayah.result.balance') }}</span>
                            <span class="text-body font-bold {{ $result['baki'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $result['baki'] >= 0 ? '+' : '' }}RM {{ number_format($result['baki'], 2) }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($result['reasoning']))
                    <div class="pt-3 border-t border-divider">
                        <p class="text-small font-medium text-text mb-2">{{ __('portal_household.hadkifayah.result.reasoning') }}</p>
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
                    <p class="text-body text-secondary">{{ __('portal_household.hadkifayah.result.empty_hint') }} <strong>{{ __('portal_household.hadkifayah.calculate') }}</strong>.</p>
                </div>
            </x-app-card>
            @endif
        </div>
    </div>
</div>
@endsection
