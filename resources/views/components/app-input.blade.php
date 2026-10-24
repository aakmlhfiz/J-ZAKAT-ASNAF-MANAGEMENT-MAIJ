@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'placeholder' => '', 'hint' => null, 'error' => null, 'icon' => null, 'iconRight' => null, 'disabled' => false])
@php $invalid = (bool) ($error ?? ($errors && $errors->has($name))); @endphp
<div>
    <label for="{{ $name }}" class="block text-small font-medium text-text mb-1.5">
        {{ $label }}
        @if($required) <span class="text-danger">*</span> @endif
    </label>
    <div class="relative">
        <input
            type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
            value="{{ $value ?? old($name) }}" placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($invalid) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'input-base' . ($invalid ? ' is-invalid' : '') . ($disabled ? ' bg-muted cursor-not-allowed text-secondary' : '')]) }}
        >
    </div>
    @if($hint && !$invalid) <p class="text-small text-secondary mt-1.5">{{ $hint }}</p> @endif
    @if($invalid) <p class="text-small text-danger mt-1.5 flex items-center gap-1.5"><x-icon name="circle-alert" :size="13" />{{ $error ?? $errors->first($name) }}</p> @endif
</div>
