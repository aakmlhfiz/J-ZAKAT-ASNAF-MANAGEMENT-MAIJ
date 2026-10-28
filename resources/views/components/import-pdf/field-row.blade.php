@props([
    'field' => '',
    'label' => '',
    'value' => '',
    'confidence' => 0,
    'sourceLabel' => null,
    'needsReview' => false,
    'editable' => true,
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $confidenceInt = (int) $confidence;
    $borderClass = $needsReview ? 'border-warning ring-1 ring-warning/20' : '';
    $bgClass = $needsReview ? 'bg-warning-soft' : '';
@endphp

<div x-data="{ editing: false, val: @js($value) }" class="flex items-start gap-3 py-3 {{ $needsReview ? 'px-3 rounded-[14px] bg-warning-soft/30 border border-warning/15' : '' }}">
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2 mb-1">
            <label class="text-small font-semibold text-text">{{ $label }}</label>
            @if($sourceLabel)
                <span class="text-eyebrow text-tertiary bg-muted px-2 py-0.5 rounded-pill" title="{{ __('imports.preview.field_source_title') }}">
                    {{ __('imports.preview.field_source_prefix') }} "{{ $sourceLabel }}"
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($icon)
                <i class="fa-solid {{ $icon }} text-tertiary text-[13px] shrink-0"></i>
            @endif

            <div x-show="!editing" class="flex-1 flex items-center gap-2 min-w-0">
                <span class="text-body text-text font-medium truncate" :class="{ 'text-tertiary': !val }">
                    <span x-text="val || '—'"></span>
                </span>
                @if($editable)
                    <button @click="editing = true" type="button" class="w-7 h-7 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-fast shrink-0" title="{{ __('common.actions.edit') }}">
                        <i class="fa-solid fa-pen text-[11px]"></i>
                    </button>
                @endif
            </div>

            <div x-show="editing" class="flex-1 flex items-center gap-2" x-cloak>
                <input type="{{ $type }}" name="{{ $field }}" x-model="val"
                       class="input-base flex-1 py-1.5 text-[13px] {{ $needsReview ? 'border-warning' : '' }}"
                       placeholder="{{ $placeholder }}">
                <button @click="editing = false" type="button" class="w-7 h-7 rounded-[8px] hover:bg-muted flex items-center justify-center text-tertiary hover:text-text transition-fast shrink-0" title="{{ __('common.actions.save') }}">
                    <i class="fa-solid fa-check text-[11px]"></i>
                </button>
            </div>

            @if(!$editable)
                <input type="hidden" name="{{ $field }}" :value="val">
            @endif
        </div>
    </div>

    <div class="shrink-0 w-[160px] pt-1">
        <x-import-pdf.confidence-meter :confidence="$confidenceInt" />
    </div>
</div>
