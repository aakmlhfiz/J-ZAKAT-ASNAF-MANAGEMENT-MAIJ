@extends('layouts.app', [
    'title' => __('hadkifayah.settings.title'),
    'subtitle' => __('hadkifayah.settings.subtitle'),
])

@section('content')
    @php
        $categoryLabels = __('hadkifayah.categories');
        $subcategoryLabels = __('hadkifayah.subcategories');
    @endphp

    @if (session('success'))
        <div class="mb-6 p-4 surface flex items-center gap-3 animate-fade-in" style="background-color: var(--color-success-soft);">
            <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0" style="background-color: rgba(52, 199, 89, 0.15); color: var(--color-success);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <p class="text-body text-text font-medium flex-1">{{ session('success') }}</p>
        </div>
    @endif

    <x-page-header :title="__('hadkifayah.settings.title')" :subtitle="__('hadkifayah.settings.subtitle')" :eyebrow="__('hadkifayah.settings.eyebrow')" icon="fa-sliders" />

    <form method="POST" action="{{ route('hadkifayah.settings.update') }}">
        @csrf

        <div class="space-y-6 stagger">
            @foreach ($order as $kategori)
                @php
                    $rows = $grouped->get($kategori, collect());
                @endphp
                @if ($rows->isNotEmpty())
                    <x-app-card :hover="false">
                        <header class="flex items-center gap-3 mb-5">
                            <div class="w-11 h-11 rounded-[8px] bg-primary-soft text-primary flex items-center justify-center ring-1 ring-primary/10">
                                <i class="fa-solid fa-sliders text-[15px]"></i>
                            </div>
                            <div>
                                <h2 class="text-h3 text-text">{{ $categoryLabels[$kategori] ?? $kategori }}</h2>
                                <p class="text-small text-secondary mt-0.5">{{ __('hadkifayah.settings.rates_count', ['count' => $rows->count()]) }}</p>
                            </div>
                        </header>

                        <div class="overflow-x-auto">
                            <table class="app-table is-compact">
                                <thead>
                                    <tr>
                                        <th>{{ __('hadkifayah.settings.subcategory') }}</th>
                                        <th class="text-right">{{ __('hadkifayah.settings.value') }}</th>
                                        <th class="text-center">{{ __('hadkifayah.settings.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr>
                                            <td>
                                                <span class="text-body text-text font-medium">{{ $subcategoryLabels[$row->subkategori] ?? ($row->subkategori ?? '—') }}</span>
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-2 justify-end">
                                                    <span class="text-body text-secondary font-medium">RM</span>
                                                    <input
                                                        type="number"
                                                        name="rows[{{ $row->id }}][nilai]"
                                                        value="{{ number_format($row->nilai, 2, '.', '') }}"
                                                        step="0.01"
                                                        min="0"
                                                        class="input-base w-24 sm:w-32 text-right tabular-nums py-2 text-[14px]"
                                                        required
                                                    >
                                                </div>
                                                <input type="hidden" name="rows[{{ $row->id }}][id]" value="{{ $row->id }}">
                                            </td>
                                            <td class="text-center">
                                                <x-toggle-switch name="rows[{{ $row->id }}][status]" :checked="$row->status === 'active'" on-value="active" off-value="inactive" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-app-card>
                @endif
            @endforeach

            <div class="sticky bottom-0 -mx-6 md:-mx-10 -mb-6 md:-mb-8 px-6 md:px-10 py-4 bg-bg border-t border-divider flex items-center gap-3 flex-wrap">
                <x-app-button type="submit" variant="primary" size="lg" icon="fa-floppy-disk">{{ __('hadkifayah.settings.save') }}</x-app-button>
                <x-app-button variant="outline" size="lg" icon="fa-calculator" href="{{ route('hadkifayah') }}">{{ __('hadkifayah.settings.calculator') }}</x-app-button>
                <span class="ml-auto text-small text-tertiary">{{ __('hadkifayah.settings.note') }}</span>
            </div>
        </div>
    </form>
@endsection
